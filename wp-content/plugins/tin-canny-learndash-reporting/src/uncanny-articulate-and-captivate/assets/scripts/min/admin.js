"use strict";

/* eslint-disable no-undef, func-names */
jQuery(document).ready($ => {
  var contentLibraryTable = '#snc-content_library_wrap table';
  $("".concat(contentLibraryTable, " a.content_title, ").concat(contentLibraryTable, " a.show")).click(function (e) {
    e.preventDefault();
    var id = $(this).attr('data-item_id');
    $('.embed_information').each(function () {
      var theId = $(this).attr('data-item_id');
      if (id !== theId) {
        $(this).hide();
      } else {
        $(this).toggle();
      }
    });
  });
});
"use strict";

/* eslint-disable no-undef, no-restricted-globals, no-alert, func-names */
function deleteSncFromTableContent(itemId, mode) {
  if (confirm('Do you really want to delete this?')) {
    var data = {
      action: 'SnC_Content_Delete',
      item_id: itemId,
      mode,
      security: jQuery('#snc-content_library_wrap input[name="security"]').val()
    };
    jQuery.post(ajaxurl, data, () => {
      if (mode === 'media library' || mode === 'vc') {
        jQuery("#snc-content_library_wrap table tr[data-item_id=\"".concat(itemId, "\"]")).remove();
      } else {
        location.reload();
      }
    });
  }
}
jQuery(document).ready($ => {
  // <-- Delete
  $('#snc-content_library_wrap table a.delete').click(function (e) {
    e.preventDefault();
    var mode = $('#snc-content_library_wrap').length ? 'media library' : 'upload form';
    var itemId = $(this).attr('data-item_id');
    deleteSncFromTableContent(itemId, mode);
  });
  // Delete -->

  //Replace content pop-up settings!!
  var itemId = '';
  $('#snc-content_library_wrap table a.snc_replace_confirm').click(function (e) {
    e.preventDefault();
    var $itemId = $(this).data('item_id');

    // Add ID to the buttons
    $('#snc-delete-book-only').data('item_id', $itemId);
    $('#snc-delete-all-data').data('item_id', $itemId);
    $('#replace_placeholder').attr('href', 'media-upload.php?content_id=' + $itemId + '&type=snc&tab=upload&min-height=400&no_tab=1&TB_iframe=true').attr('data-item_id', $itemId);
  });

  //
  $('.tclr-replace-content__task-btn').on('click', function () {
    // Get button
    var $button = $(this);

    // Get task
    var task = $button.data('task');

    // Hide step 1 container, and show the step 2 container
    $('.tclr-replace-content__step-1').hide();
    $('.tclr-replace-content__step-2').show();

    // Hide the container of both tasks
    $('#bookmark-confirmation, #all-confirmation').hide();

    // Check the task
    if (task === 'remove-bookmark') {
      // Show container
      $('#bookmark-confirmation').show();
    } else if (task === 'remove-all-data') {
      $('#all-confirmation').show();
    }
  });

  //
  $('.tclr-replace-content__cancel-2-step-btn').on('click', function () {
    // Hide the container of both tasks
    $('#bookmark-confirmation, #all-confirmation').hide();

    // Show the container of the first step and hide the container of the second one
    $('.tclr-replace-content__step-1').show();
    $('.tclr-replace-content__step-2').hide();
  });

  //
  $('#snc-delete-book-only').click(function () {
    var $button = $(this);
    itemId = $(this).data('item_id');
    $button.addClass('tclr-btn--loading');
    var mode = $('#snc-content_library_wrap').length ? 'media library' : 'upload form';
    var data1 = {
      action: 'SnC_Content_Bookmark_Delete',
      item_id: itemId,
      mode,
      security: $('#snc-content_library_wrap input[name="security"]').val()
    };
    $.post(ajaxurl, data1).done(function () {
      $button.removeClass('tclr-btn--loading');
      $('.tclr-replace-content__step-1').show();
      $('.tclr-replace-content__step-2').hide();
      $('#TB_closeWindowButton').trigger('click');
      setTimeout(function () {
        $('#replace_placeholder').trigger('click');
      }, 1000);
    });
    //}
  });

  //
  $('#snc-delete-all-data').click(function () {
    var $button = $(this);
    itemId = $(this).data('item_id');
    $button.addClass('tclr-btn--loading');
    var mode = $('#snc-content_library_wrap').length ? 'media library' : 'upload form';
    var data2 = {
      action: 'SnC_Content_Delete_All',
      item_id: itemId,
      mode,
      security: $('#snc-content_library_wrap input[name="security"]').val()
    };
    $.post(ajaxurl, data2).done(function () {
      $button.removeClass('tclr-btn--loading');
      $('.tclr-replace-content__step-1').show();
      $('.tclr-replace-content__step-2').hide();
      $('#TB_closeWindowButton').trigger('click');
      setTimeout(function () {
        $('#replace_placeholder').trigger('click');
      }, 1000);
    });
    //}
  });

  /* ES5 */
  var TinCannyModulesSearch = {
    init: function init() {
      // Get elements
      this.getElements();

      // Get required data
      this.getData();

      // Create Fuse instance
      this.initFuse();

      // Search
      this.listenSearchField();
    },
    getElements: function getElements() {
      this.$elements = {
        searchField: $('#tclr-classic-editor-content-library-search'),
        tableContainer: $('.tclr-classic-editor-content-library__list'),
        tableItems: $('.tclr-classic-editor-content-library__list tbody tr.tclr-classic-editor-content-library__item')
      };
    },
    getData: function getData() {
      // Create an array of objects with the Tin Canny content items
      var items = [];

      // Iterate DOM elements (tr)
      $.each(this.$elements.tableItems, function (index, tr) {
        var $tr = $(tr);

        // Get row data
        var rowData = {
          title: $tr.data('item_name'),
          $element: $tr
        };

        // Add the row to the main items array
        items.push(rowData);
      });
      this.items = items;
    },
    initFuse: function initFuse() {
      this.Fuse = new Fuse(this.items, {
        keys: ['title'],
        threshold: 0,
        ignoreLocation: true
      });
    },
    listenSearchField: function listenSearchField() {
      // Reference to this instance
      var thisRef = this;
      this.$elements.searchField.on('input', function () {
        // Get the value of the search field
        var searchFieldValue = thisRef.$elements.searchField.val();

        // Check if the search field has a value
        if (searchFieldValue !== '') {
          // Search
          thisRef.search(searchFieldValue);
        } else {
          // Otherwise, show all
          thisRef.showAll();
        }
      });
    },
    search: function search(searchQuery) {
      // Perform search
      var filteredItems = this.Fuse.search(searchQuery);

      // Enable search mode
      // We're adding a class to the container to hide all the rows
      // and we're going to iterate through the ones we have to show
      // to add a class to them. We're doing this to reduce the number
      // of DOM modifications
      this.$elements.tableContainer.addClass('tclr-classic-editor-content-library__list--search-mode');

      // Remove the class to show the items from all the items
      this.$elements.tableItems.removeClass('tclr-classic-editor-content-library__item--visible');

      // Check if there are results
      if (filteredItems.length > 0) {
        // Hide the "no results" row
        this.$elements.tableContainer.removeClass('tclr-classic-editor-content-library__list--no-results');

        // Iterate the results
        filteredItems.forEach(function (row) {
          // Add class to the item to show it
          row.item.$element.addClass('tclr-classic-editor-content-library__item--visible');
        });
      } else {
        // Show the "no results" row
        this.$elements.tableContainer.addClass('tclr-classic-editor-content-library__list--no-results');
      }
    },
    showAll: function showAll() {
      // Disable search mode
      this.$elements.tableContainer.removeClass('tclr-classic-editor-content-library__list--search-mode');

      // Remove the class to show the items from all the items
      this.$elements.tableItems.removeClass('tclr-classic-editor-content-library__item--visible');

      // Hide the "no results" row
      this.$elements.tableContainer.removeClass('tclr-classic-editor-content-library__list--no-results');
    }
  };
  TinCannyModulesSearch.init();
});
"use strict";

/* eslint-disable no-undef, no-restricted-globals, no-alert, func-names */
function deleteSncFromTable(itemId, mode) {
  if (confirm('Do you really want to delete this?')) {
    var data = {
      action: 'SnC_Media_Delete',
      item_id: itemId,
      mode,
      security: jQuery('form.snc-media_enbed_form input[name="security"]').val()
    };
    jQuery.post(ajaxurl, data, () => {
      if (mode === 'media library' || mode === 'vc') {
        jQuery("#snc-content_library_wrap table tr[data-item_id=\"".concat(itemId, "\"]")).remove();
      } else {
        location.reload();
      }
    });
  }
}
jQuery(document).ready($ => {
  // <-- Lightbox Options
  $('.insert_type input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');
    $("form[data-item_id=\"".concat(key, "\"] .options")).stop().slideUp();
    $("form[data-item_id=\"".concat(key, "\"] .options[data-item_option=\"").concat($(this).val(), "\"]")).stop().slideDown();
  });
  $('.lightbox_title input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');
    var val = $(this).val();
    $("input.text_with_title[data-item_id=\"".concat(key, "\"]")).hide();
    if (val === 'With Title') {
      $("input.text_with_title[data-item_id=\"".concat(key, "\"]")).show().focus();
    }
  });
  $('.lightbox_button input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');
    var val = $(this).val();
    $("input.lightbox_button_text[data-item_id=\"".concat(key, "\"]")).hide();
    $("div.lightbox_button_text[data-item_id=\"".concat(key, "\"]")).hide();
    $("section.lightbox_button_custom[data-item_id=\"".concat(key, "\"]")).hide();
    $("input.lightbox_button_url[data-item_id=\"".concat(key, "\"]")).hide();
    if (val === 'text' || val === 'small' || val === 'medium' || val === 'large') {
      $("input.lightbox_button_text[data-item_id=\"".concat(key, "\"]")).show();
      $("div.lightbox_button_text[data-item_id=\"".concat(key, "\"]")).show();
    }
    if (val === 'url') {
      $("input.lightbox_button_url[data-item_id=\"".concat(key, "\"]")).show();
    }
    if (val === 'image') {
      $(".lightbox_button_custom[data-item_id=\"".concat(key, "\"]")).show();
    }
  });
  $('input[name="global_lightbox"]').change(function () {
    var $parent = $(this).closest('.lightbox_size_options');
    var $widthSetting = $parent.find('.lightbox_width_settings');
    var $heightSetting = $parent.find('.lightbox_height_settings');
    if ($(this).is(':checked')) {
      $widthSetting.hide();
      $heightSetting.hide();
    } else {
      $widthSetting.show();
      $heightSetting.show();
    }
  });

  // <-- New Window Options
  $('.new_window_option input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');
    var val = $(this).val();
    $(".new_window_option[data-item_id=\"".concat(key, "\"] input[type=\"text\"]")).hide();
    $("div._blank_button_text[data-item_id=\"".concat(key, "\"]")).hide();
    $(".new_window_option[data-item_id=\"".concat(key, "\"] .snc_custom_image_upload")).hide();
    if (val === 'text' || val === 'small' || val === 'medium' || val === 'large') {
      $(".new_window_option[data-item_id=\"".concat(key, "\"] input._blank_text")).show();
      $("div._blank_button_text[data-item_id=\"".concat(key, "\"]")).show();
    }
    if (val === 'image') {
      $(".new_window_option[data-item_id=\"".concat(key, "\"] .snc_custom_image_upload")).show();
    }
    if (val === 'url') {
      $(".new_window_option[data-item_id=\"".concat(key, "\"] input._blank_url")).show();
    }
  });
  // New Window Options -->

  // <-- Same Window Options
  $('.same_window_option input[type="radio"]').click(function () {
    var key = $(this).attr('data-item_id');
    var val = $(this).val();
    $(".same_window_option[data-item_id=\"".concat(key, "\"] input[type=\"text\"]")).hide();
    $("div._self_button_text[data-item_id=\"".concat(key, "\"]")).hide();
    $(".same_window_option[data-item_id=\"".concat(key, "\"] .snc_custom_image_upload")).hide();
    if (val === 'text' || val === 'small' || val === 'medium' || val === 'large') {
      $(".same_window_option[data-item_id=\"".concat(key, "\"] input._self_text")).show();
      $("div._self_button_text[data-item_id=\"".concat(key, "\"]")).show();
    }
    if (val === 'image') {
      $(".same_window_option[data-item_id=\"".concat(key, "\"] .snc_custom_image_upload")).show();
    }
    if (val === 'url') {
      $(".same_window_option[data-item_id=\"".concat(key, "\"] input._self_url")).show();
    }
  });
  // Same Window Options -->

  // <-- Delete
  $('form.snc-media_enbed_form .delete-media, #snc-content_library_wrap table span a.delete').click(function (e) {
    e.preventDefault();
    var mode = $('#snc-content_library_wrap').length ? 'media library' : 'upload form';
    var itemId = $(this).attr('data-item_id');
    deleteSncFromTable(itemId, mode);
  });
  // Delete -->

  // <-- Custom Image Upload.
  $('.snc_custom_image_upload button').click(function (e) {
    e.preventDefault();
    var $parent = $(this).closest('.snc_custom_image_upload');
    var $fileInput = $parent.find('input[type="file"]');
    $fileInput.click();
  });
  $('.snc_custom_image_upload input[type="file"]').on("change", function () {
    var $parent = $(this).closest('.snc_custom_image_upload');
    var $desc = $parent.find('button .button-description');
    var fn = $(this).val();
    $desc.text(fn.match(/[^\\/]*$/)[0]);
  });
  // Custom Image Upload. -->
});
"use strict";

/* eslint-disable no-undef, no-restricted-globals, func-names */
jQuery(document).ready($ => {
  var $sncForm = $('.snc-media_enbed_form');

  // Get Code From PHP
  $sncForm.ajaxForm({
    success: response => {
      var data = JSON.parse(response);
      var win = window.dialogArguments || opener || parent || top;
      win.send_to_editor(data.shortcode);
    }
  });
});
"use strict";

/* eslint-disable no-undef, func-names, no-restricted-globals, no-use-before-define */

// Define global variable.
var tincannyMediaUpload;

// Define trigger upload function.
class TincannyMediaUploadClass {
  constructor($, key) {
    this.$ = $;
    this.isIframe = window.self !== window.top;
    this.key = key || false;
    this.enableButton = true;
    this.ids = {
      parentId: 'snc-media_upload_file_wrap',
      uploadWrapperId: 'snc-media_upload_tincanny-uploader-wrapper',
      buttonWrapId: 'snc-media_upload_button_wrap',
      contentID: 'content_id',
      settingsWrapID: 'snc-media_upload_settings_wrap',
      fullZipUpload: 'snc-full_zip_upload',
      fullZipMax: 'snc-full_zip_upload__max'
    }, this.config = window.tincannyZipUploadData || {};
    this.addEventListeners();
  }
  addEventListeners() {
    var app = this;

    // Add Listener for Uploader Events.
    document.addEventListener("tincanny-zip-uploader-event", e => {
      var detail = e.detail || {};
      if (!detail.action) {
        return;
      }
      if (parseInt(app.config.debug)) {
        console.log({
          action: 'tincanny-zip-uploader-event-media.js',
          detail: detail
        });
      }
      if (detail.action === 'registered-tincanny-module') {
        if (detail.success) {
          app.handleUploadSuccess(detail);
        }
      }
      if (detail.action === 'upload-started-tincanny-zip') {
        app.setConfigItem('directory', detail.directory);
      }
      if (detail.action === 'cancel-tincanny-module-upload') {
        app.handleCancelledUpload(detail);
      }
    });

    // Add Click and Change Event Listeners.
    document.addEventListener('click', event => {
      if (event.target.matches('.snc-file_upload_button_wrapper button')) {
        if (!app.enableButton) {
          return;
        }
        app.setSettings(app);
        var $parentWrapper = event.target.closest('.snc-file_upload_button_wrapper');
        var $fileInput = $parentWrapper.querySelector('input[type="file"]');
        $fileInput.click();
        $fileInput.onchange = e => {
          var $contentID = app.getElByID(app.ids.contentID);
          app.showLoading();
          app.triggerZipUploaderEvent({
            action: 'upload-tincanny-zip',
            uploadWrapperID: app.ids.uploadWrapperId,
            file: e.target.files[0],
            contentID: $contentID ? $contentID.value : '',
            settings: app.getConfig('settings')
          });
          // Clear the file input value
          e.target.value = null;
        };
      } else if (event.target.matches('#tab-snc-library a')) {
        app.maybeCleanUpAbortedUploads(app);
      } else if (event.target.matches('#' + this.ids.fullZipUpload)) {
        app.setSettings(app);
      }
    });

    // Handle Aborted Uploads

    // Listen for Thickbox Close when code is triggered from iFrame or parent.
    var TBjQuery = app.isIframe ? window.parent.jQuery : app.$;
    TBjQuery("#TB_closeWindowButton, #TB_overlay").click(function () {
      app.maybeCleanUpAbortedUploads(app);
    });

    // Disable Escape Key.
    TBjQuery(document).on('keydown', function (event) {
      if (event.key === 'Escape' || event.keyCode === 27) {
        event.preventDefault();
        event.stopPropagation();
      }
    });
  }
  getElByID(id) {
    var $el = document.getElementById(id);
    return $el && $el !== null ? $el : false;
  }
  showLoading() {
    this.enableButton = false;
    this.show(this.ids.uploadWrapperId);
    this.hide(this.ids.buttonWrapId);
    this.hide(this.ids.settingsWrapID);
  }
  hideLoading() {
    this.enableButton = true;
    this.show(this.ids.buttonWrapId);
    this.show(this.ids.settingsWrapID);
    this.hide(this.ids.uploadWrapperId);
    this.setConfigItem('directory', '');
  }
  hide(id) {
    var $el = this.getElByID(id);
    if ($el) {
      $el.style.display = 'none';
    }
  }
  show(id) {
    var $el = this.getElByID(id);
    if ($el) {
      $el.style.display = 'block';
    }
  }
  setAttribute(selector, attribute, value) {
    var $el = document.querySelector(selector);
    if ($el) {
      $el.setAttribute(attribute, value);
    }
  }
  triggerZipUploaderEvent(data) {
    var event = new CustomEvent('tincanny-zip-uploader-event', {
      detail: data
    });
    document.dispatchEvent(event);
  }
  handleUploadSuccess(data) {
    this.setConfigItem('directory', '');
    var $parent = this.getElByID(this.ids.parentId);
    if (!$parent) {
      return;
    }
    var $noTab = $parent.querySelector('#no_tab');
    var $noRefresh = $parent.querySelector('#no_refresh');
    if ($noTab) {
      if ($noRefresh) {
        //self.parent.window.wp.tccmb_content($('#ele_id').val());
        self.parent.tb_remove();
      } else {
        window.parent.location = window.parent.location.href;
      }
    }

    // Handle Visual Composer.
    if (this.key && this.key !== 'undefined') {
      this.setAttribute('#vc_properties-panel .vc-snc-trigger input', 'value', data.id);
      this.setAttribute('#vc_properties-panel .vc-snc-name input', 'value', data.title);
      trigger_vc_snc_mode();
    } else {
      var $embedInfo = document.querySelector('.snc-embed_information');
      if (null !== $embedInfo) {
        $embedInfo.style.display = 'block';
        this.hide(this.ids.parentId);
      }
      this.setAttribute('a.delete-media', 'data-item_id', data.id);
      this.setAttribute('input#item_id', 'value', data.id);
      this.setAttribute('input#item_title', 'value', data.title);
    }
  }
  handleCancelledUpload() {
    this.hideLoading();
  }
  maybeCleanUpAbortedUploads(app) {
    var directory = app.getConfig('directory');
    if (directory === '') {
      return;
    }
    var config = window.top.tincannyZipUploadData;
    app.setConfigItem('directory', '');
    jQuery.ajax({
      method: "POST",
      data: {
        'action': 'cancel-tincanny-module-upload',
        'directory': directory,
        'status': 'abort',
        'fullzip': app.isfullZipUpload(app),
        'security': config.nonce
      },
      url: config.rest_url + config.rest_namespace,
      beforeSend: function beforeSend(xhr) {
        xhr.setRequestHeader('X-WP-Nonce', config.rest_nonce);
      }
    });
  }
  isfullZipUpload(app) {
    var $fullZipUpload = app.getElByID(app.ids.fullZipUpload);
    var checked = $fullZipUpload ? $fullZipUpload.checked : false;
    return checked ? 1 : 0;
  }
  setSettings(app) {
    var settings = app.getConfig('settings');
    settings = settings || {};
    settings.fullZipUpload = app.isfullZipUpload(app);
    app.setConfigItem('settings', settings);
    // Show / Hide Max File Size warning.
    var $fullZipMax = app.getElByID(app.ids.fullZipMax);
    if ($fullZipMax) {
      $fullZipMax.style.display = settings.fullZipUpload ? 'block' : 'none';
    }
  }
  getConfig(key) {
    if (typeof window.top.tincannyZipUploadData === 'undefined') {
      console.log('window.top.tincannyZipUploadData is undefined');
      window.top.tincannyZipUploadData = this.config;
    }

    // check if key is undefined.
    if (!key || typeof key === 'undefined') {
      return window.top.tincannyZipUploadData;
    }
    if (typeof key === 'string') {
      return window.top.tincannyZipUploadData[key] || '';
    }
    return '';
  }
  setConfigItem(key, data) {
    if (typeof window.top.tincannyZipUploadData === 'undefined') {
      console.log('window.top.tincannyZipUploadData is undefined');
      window.top.tincannyZipUploadData = this.config;
    }
    window.top.tincannyZipUploadData[key] = data;
  }
}
jQuery(document).ready($ => {
  var key = false; // review test VC to understand this setup.
  tincannyMediaUpload = new TincannyMediaUploadClass($, key);
});
"use strict";