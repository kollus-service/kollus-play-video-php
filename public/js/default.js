$(".alert-dismissible").fadeTo(2000, 500).slideUp(500, function () {
  $(".alert-dismissible").alert('close');
});

/**
 * Show Instant Message.
 *
 * @param {string} type - success|info|warning|danger
 * @param {string} message
 */
function showAlert(type, message) {
  var alertDiv = $(
    '<div class="alert alert-' + type + ' alert-dismissible">\n' +
    '    <button type="button" class="close" data-dismiss="alert">&times;</button>' + message + '\n' +
    '</div>\n'
    ),
    delayDuration = 3000,
    slideUpDuration = 500;

  alertDiv.delay(delayDuration).slideUp(slideUpDuration);

  $('.flashes').append(alertDiv);
}

/**
 * showModal
 *
 * @param {string} modalContent
 * @param {object} options
 * @param {string} modalDivId
 */
function showModal(modalContent, options, modalDivId) {
  var modalDiv;

  modalDivId = modalDivId || 'kollus-modal';
  options = options || {};

  modalDiv = $('#' + modalDivId);
  if (!modalDiv.length) {
    modalDiv = $(
      '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">' +
      '    <div class="modal-dialog modal-lg">' +
      '        <div class="modal-content"></div>' +
      '    </div>' +
      '</div>'
    );

    modalDiv.attr('id', modalDivId);
    modalDiv.on('hidden.bs.modal', function(e) {
      // when iframe closed at IE, video in video does not stop.
      // so, iframe's src must set 'null'
      $('#' + modalDivId + ' .modal-body iframe').attr('src', null);
      $('#' + modalDivId + ' .modal-body').html('');
    });
    $('body').append(modalDiv);

    modalDiv.modal(options);
  }

  modalDiv.find('.modal-content').html(modalContent);
  modalDiv.modal('show');
}

/**
 * hideModal
 *
 * @param {string} modalDivId
 */
function hideModal(modalDivId) {
  var modalDiv;

  modalDivId = modalDivId || 'kollus-modal';

  modalDiv = $('#' + modalDivId);
  if (modalDiv.length) {
    modalDiv.modal('hide');
  }
}

$(document).on('change', 'select[data-action=channel-selector]', function(e) {
  e.preventDefault();

  $(location).attr('href', '/channel/' + $(this).val());
});

$(document).on('click', 'button[data-action=modal-play-video]', function(e) {
  e.preventDefault();

  var self = this,
    uploadFileKey = $(self).data('upload-file-key'),
    modalContent;

  $.post('/auth/play-video-url/' + channelKey + '/' + uploadFileKey, function (data) {
    modalContent = $(
      '<div class="modal-header">\n' +
      '    <button type="button" class="close" data-dismiss="modal">&times;</button>\n' +
      '    <h4 class="modal-title">' + data.title + '</h4>\n' +
      '</div>\n'+
      '<div class="modal-body">\n' +
      '    <div class="embed-responsive embed-responsive-16by9">\n' +
      '        <iframe src="' + data.web_token_url + '" class="embed-responsive-item" allowfullscreen></iframe>\n' +
      '    </div>\n' +
      '</div>\n' +
      '<div class="modal-footer">\n' +
      '    <div class="btn-group">\n' +
      '        <a href="' + data.web_token_url + '" class="btn btn-warning" target="_blank"><span class="fa fa-link"> Link</a>\n' +
      '        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-times"></span> Close</button>\n' +
      '    </div>\n' +
      '</div>'
    );

    showModal(modalContent)
  });
});

$(document).on('click', 'button[data-action=modal-download-video]', function(e) {
  e.preventDefault();

  var self = this,
    uploadFileKey = $(self).data('upload-file-key'),
    modalContent;

  showAlert('warning', 'If your platform is mac osx or media is not encrypted, it will be streaming.');

  $.post('/auth/download-video-url/' + channelKey + '/' + uploadFileKey, function (data) {
    modalContent = $(
      '<div class="modal-header">\n' +
      '    <button type="button" class="close" data-dismiss="modal">&times;</button>\n' +
      '    <h4 class="modal-title">' + data.title + '</h4>\n' +
      '</div>\n'+
      '<div class="modal-body">' +
      '    <div class="embed-responsive embed-responsive-16by9">\n' +
      '        <iframe src="' + data.web_token_url + '" class="embed-responsive-item" allowfullscreen></iframe>\n' +
      '    </div>\n' +
      '</div>\n' +
      '<div class="modal-footer">\n' +
      '    <div class="btn-group">\n' +
      '        <a href="' + data.web_token_url + '" class="btn btn-warning" target="_blank"><span class="fa fa-link"> Link</a>\n' +
      '        <button type="button" class="btn btn-default" data-dismiss="modal"><span class="fa fa-times"></span> Close</button>\n' +
      '    </div>\n' +
      '</div>'
    );

    showModal(modalContent)
  });
});

$(document).on('click', 'button[data-action=call-download-multi-video]', function(e) {
  e.preventDefault();

  var self = this,
    checkedItems = $('input[type=checkbox][data-action=download-item]:checked'),
    postDatas = {
      selected_media_items: []
    };

  if (!checkedItems.length) {
    showAlert('warning', 'Select the item you want to download.');
    return;
  }

  showAlert('warning', 'You must install kollus player.<br />And if your platform is mac osx or more environments, is not working.');

  checkedItems.each(function(index, element) {
    uploadFileKey = $(element).val();

    postDatas.selected_media_items.push({
      upload_file_key: uploadFileKey
    });
  });

  $.post('/auth/download-multi-video/' + channelKey, postDatas, function (data) {
    document.location.href = 'kollus://download?url=' + encodeURIComponent(data.web_token_url);
  });
});

$(document).on('click', 'button[data-action=call-play-video-playlist]', function(e) {
  e.preventDefault();

  var self = this,
    checkedItems = $('input[type=checkbox][data-action=download-item]:checked'),
    postDatas = {
      selected_media_items: []
    };

  if (!checkedItems.length) {
    showAlert('warning', 'Select the item you want to add to playlist.');
    return;
  }

  showAlert('warning', 'You must install kollus player.');

  checkedItems.each(function(index, element) {
    uploadFileKey = $(element).val();

    postDatas.selected_media_items.push({
      upload_file_key: uploadFileKey
    });
  });

  $.post('/auth/play-video-playlist/' + channelKey, postDatas, function (data) {
    document.location.href = 'kollus://path?url=' + encodeURIComponent(data.web_token_url);
  });
});
