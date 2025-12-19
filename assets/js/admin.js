/**
 * CDM Explorer - Admin JavaScript
 * 
 * @package CDM_Explorer
 */

(function($) {
    'use strict';

    const CDMAdmin = {
        
        init: function() {
            this.bindEvents();
            this.initMediaUploader();
        },
        
        bindEvents: function() {
            // Validate URL button
            $('#cdm-validate-btn').on('click', this.validateUrl.bind(this));
            
            // Import collections button
            $('#import-collections-btn').on('click', this.importCollections.bind(this));
            
            // Import items for single collection
            $(document).on('click', '.import-items-btn', this.importItems.bind(this));
            
            // Select all collections checkbox
            $('#select-all-collections').on('change', function() {
                $('input[name="collections[]"]').prop('checked', $(this).is(':checked'));
                CDMAdmin.updateSelectedBtn();
            });
            
            // Collection checkboxes
            $(document).on('change', 'input[name="collections[]"]', this.updateSelectedBtn);
            
            // Import selected collections
            $('#import-selected-btn').on('click', this.importSelectedCollections.bind(this));
        },
        
        /**
         * Initialize WordPress Media Uploader for preview image
         */
        initMediaUploader: function() {
            let mediaUploader;
            
            // Upload/Change image button
            $('#cdm_upload_preview_image').on('click', function(e) {
                e.preventDefault();
                
                // If the uploader object has already been created, reopen the dialog
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                // Create the media uploader
                mediaUploader = wp.media({
                    title: cdmExplorer.strings.selectImage || 'Select Preview Image',
                    button: {
                        text: cdmExplorer.strings.useImage || 'Use this image'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                // When an image is selected, run a callback
                mediaUploader.on('select', function() {
                    const attachment = mediaUploader.state().get('selection').first().toJSON();
                    
                    // Set the image URL and ID
                    $('#cdm_preview_image').val(attachment.url);
                    $('#cdm_preview_image_id').val(attachment.id);
                    
                    // Update the preview
                    const $container = $('.cdm-preview-image-container');
                    $container.html('<img src="' + attachment.url + '" alt="" style="max-width: 100%; height: auto; display: block;">');
                    
                    // Update button text
                    $('#cdm_upload_preview_image').text('Change Image');
                    
                    // Show remove button if not visible
                    if ($('#cdm_remove_preview_image').length === 0) {
                        $('#cdm_upload_preview_image').after(
                            '<button type="button" class="button cdm-remove-preview-image" id="cdm_remove_preview_image">Remove</button>'
                        );
                    }
                });
                
                // Open the uploader dialog
                mediaUploader.open();
            });
            
            // Remove image button
            $(document).on('click', '#cdm_remove_preview_image', function(e) {
                e.preventDefault();
                
                // Clear the image URL and ID
                $('#cdm_preview_image').val('');
                $('#cdm_preview_image_id').val('');
                
                // Update the preview - check for CDM image fallback
                const $container = $('.cdm-preview-image-container');
                const cdmImageUrl = $('#cdm_image_url').val();
                
                if (cdmImageUrl) {
                    $container.html(
                        '<img src="' + cdmImageUrl + '" alt="" style="max-width: 100%; height: auto; display: block; opacity: 0.6;">' +
                        '<p class="description" style="margin-top: 5px;">Current image from ContentDM</p>'
                    );
                } else {
                    $container.html('<div style="background: #f0f0f1; padding: 20px; text-align: center; color: #646970;">No image set</div>');
                }
                
                // Update button text
                $('#cdm_upload_preview_image').text('Upload Image');
                
                // Remove the remove button
                $(this).remove();
            });
        },
        
        validateUrl: function(e) {
            e.preventDefault();
            
            const $btn = $('#cdm-validate-btn');
            const $status = $('#cdm-connection-status');
            const url = $('#cdm-url').val().trim();
            
            if (!url) {
                this.showStatus($status, 'error', 'Please enter a URL');
                return;
            }
            
            $btn.prop('disabled', true).text(cdmExplorer.strings.validating);
            $status.hide();
            
            $.ajax({
                url: cdmExplorer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdm_validate_url',
                    nonce: cdmExplorer.nonce,
                    url: url
                },
                success: function(response) {
                    if (response.success) {
                        CDMAdmin.showStatus($status, 'success', 
                            'Connected! Found ' + response.data.total + ' collections.');
                    } else {
                        CDMAdmin.showStatus($status, 'error', response.data.message);
                    }
                },
                error: function() {
                    CDMAdmin.showStatus($status, 'error', cdmExplorer.strings.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Connect');
                }
            });
        },
        
        importCollections: function(e) {
            e.preventDefault();
            
            const $btn = $('#import-collections-btn');
            const $status = $('#import-collections-status');
            
            $btn.prop('disabled', true);
            $status.show();
            this.updateProgress($status, 0, cdmExplorer.strings.importing);
            
            $.ajax({
                url: cdmExplorer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdm_import_collections',
                    nonce: cdmExplorer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        CDMAdmin.updateProgress($status, 100, 
                            'Imported: ' + data.imported + ', Updated: ' + data.updated + ', Total: ' + data.total);
                        
                        // Reload page to show new collections
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        CDMAdmin.updateProgress($status, 0, 'Error: ' + response.data.message);
                    }
                },
                error: function() {
                    CDMAdmin.updateProgress($status, 0, cdmExplorer.strings.error);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        },
        
        importItems: function(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const alias = $btn.data('alias');
            const maxItems = $('#max-items').val() || 100;
            const $row = $btn.closest('tr');
            const $status = $('#import-items-status');
            
            $btn.prop('disabled', true).text('Importing...');
            $status.show();
            this.updateProgress($status, 50, 'Importing items from ' + alias + '...');
            
            $.ajax({
                url: cdmExplorer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdm_import_items',
                    nonce: cdmExplorer.nonce,
                    alias: alias,
                    max_items: maxItems
                },
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        CDMAdmin.updateProgress($status, 100, 
                            'Imported: ' + data.imported + ', Updated: ' + data.updated + ' items');
                        
                        // Update count in table
                        $row.find('.imported-count').text(data.imported + data.updated);
                    } else {
                        CDMAdmin.updateProgress($status, 0, 'Error: ' + response.data.message);
                    }
                },
                error: function() {
                    CDMAdmin.updateProgress($status, 0, cdmExplorer.strings.error);
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Import Items');
                }
            });
        },
        
        importSelectedCollections: async function(e) {
            e.preventDefault();
            
            const selected = $('input[name="collections[]"]:checked');
            
            if (selected.length === 0) {
                alert('Please select at least one collection');
                return;
            }
            
            if (!confirm(cdmExplorer.strings.confirmImport)) {
                return;
            }
            
            const $btn = $('#import-selected-btn');
            const $status = $('#import-items-status');
            const maxItems = $('#max-items').val() || 100;
            
            $btn.prop('disabled', true);
            $status.show();
            
            let completed = 0;
            const total = selected.length;
            
            for (let i = 0; i < selected.length; i++) {
                const alias = $(selected[i]).val();
                
                this.updateProgress($status, (completed / total) * 100, 
                    'Importing ' + alias + ' (' + (completed + 1) + '/' + total + ')...');
                
                try {
                    await this.importItemsAsync(alias, maxItems);
                    completed++;
                } catch (error) {
                    console.error('Error importing ' + alias, error);
                }
            }
            
            this.updateProgress($status, 100, 'Completed! Imported ' + completed + ' collections.');
            $btn.prop('disabled', false);
            
            // Refresh stats
            this.refreshStats();
        },
        
        importItemsAsync: function(alias, maxItems) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: cdmExplorer.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cdm_import_items',
                        nonce: cdmExplorer.nonce,
                        alias: alias,
                        max_items: maxItems
                    },
                    success: function(response) {
                        if (response.success) {
                            resolve(response.data);
                        } else {
                            reject(response.data.message);
                        }
                    },
                    error: function() {
                        reject('Network error');
                    }
                });
            });
        },
        
        updateSelectedBtn: function() {
            const count = $('input[name="collections[]"]:checked').length;
            $('#import-selected-btn').prop('disabled', count === 0);
        },
        
        showStatus: function($el, type, message) {
            $el.removeClass('cdm-status--success cdm-status--error cdm-status--loading')
               .addClass('cdm-status--' + type)
               .show();
            $el.find('.cdm-status__text').text(message);
        },
        
        updateProgress: function($el, percent, message) {
            $el.find('.cdm-progress__bar').css('width', percent + '%');
            $el.find('.cdm-import-status__text').text(message);
        },
        
        refreshStats: function() {
            $.ajax({
                url: cdmExplorer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cdm_get_import_status',
                    nonce: cdmExplorer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#stat-collections').text(response.data.collections);
                        $('#stat-items').text(response.data.items);
                    }
                }
            });
        }
    };
    
    $(document).ready(function() {
        CDMAdmin.init();
    });
    
})(jQuery);

