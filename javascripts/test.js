function removeFromDom() {
  $(this).remove();
}

jQuery(function($) {
    $('ul.answers').livequery(function() {
      $(this).sortable({
        /*containment: 'parent', zindex: 10, */
        items: 'li',
        handle: '.handle',
        update: function() {
            $(this).find('li').each(function(i) {
                if ($(this).find('input[name$=text]').val() || $(this).find('input[name$=code]').val()) {
                    $(this).find('input[name$=order]').val(i+1);
                }
            });
        }
      })
      .find('.handle').css('cursor', 'move').end()
      .find('input[name$=order]').hide();
    });
});

jQuery(function($) {
    $('#questions').sortable({
        /*containment: 'parent', zindex: 10, */
        items: '.question',
        handle: '.handle',
        update: function() {
            $(this).find('.question').each(function(i) {
              $(this).find('input[name*=order]').val(i+1);
            });
            var data = $(this).find('.question input[name*=order]').serialize();
            var testId = $('#test_id').val();
            $.post('/admin/tests/' + testId + '/questions/reorder', data);
        }
      })
      .find('.handle').css('cursor', 'move').end()
      .find('input[name$=order]').hide();
});

jQuery(function($) {
  $('.question').live('click', function() {
    var editorId = this.id + "_editor";
    var existing = $('.editor');
    closeEditor(existing);
    if (existing.is('#' + editorId))
      return false;
    var qid = this.id.split("_")[1];
    var test_id = $('#test_id').val();
    var that = this;
    $.get('/admin/tests/' + test_id + '/questions/' + qid + '/edit', {}, function(html) {
      $(that).after(html);
      $('.question_editor', $("#" + editorId)).submit(function() {
        var data = $(this).serialize();
        $.post('/admin/tests/' + test_id + '/questions/' + qid + '/edit', data, function() {}, "script");
        return false;
      });
    }, 'html');
  });
  $('#new_question')
  
  $('.cancel_editor').live('click', function() {
    closeEditor($(this).closest('.editor'));
    return false;
  });
  
  $('.remove_answer').live('click', function() {
    $(this).closest('li')
      .find('input[name$=text]').val('').end()
      .find('input[name$=code]').val('').end()
      .fadeOut("slow");
    return false;
  });
  $('.remove_question').live('click', function() {
    var q = $(this).closest('.question');
    if (!confirm("Удалить вопрос «"+$('span.text', q).text()+"»?"))
      return false;
    var testId = $('#test_id').val();
    var id = q.attr('id').split('_')[1];
    $.post('/admin/tests/'+testId+'/questions/'+id+'/delete', {}, function() {
      q.fadeOut("slow");
    });
    return false;
  });
  $('#delete_test').click(function() {
    if (!confirm("Отменить удаление будет невозможно. Удалить тест?"))
      return false;
    $.post('/admin/tests/' + $('#test_id').val() + '/delete', {}, function() {
      window.location.href = '/admin/';
    });
    return false;
  });
  
  $('img.fileupload').livequery(function() {
    var img = this;
    new AjaxUpload(img, {
      onComplete: function(file, response) {
        if (response == 'error')
          alert("Извините, не удалось закачать ваш файл.");
        else {
    			img.src = '/tmp/uploads/' + response;
          var codeField = $('#' + img.id + '_code');
    			codeField.val(response);
  			}
  		}
    });
  });
});

function closeEditor(editor) {
  $('.question_editor', $(editor)).slideUp("fast", function() {
    $(editor).remove();
  });
}

function questionNotFound(question_id) {
  alert('Извините, этот вопрос уже удален.');
  closeEditor($('.editor'));
  $('question_' + question_id).fadeOut();
}

function questionSaved(question_id, inserted) {
  closeEditor($('.editor'));
  var testId = $('#test_id').val();
  $.get('/admin/tests/' + testId + '/questions/' + question_id + '/', {}, function(text) {
    if (inserted) {
      $('#question_new').before(text);
    } else {
      $('#question_'+question_id).replaceWith(text);
    }
    $('#question_' + question_id).effect('highlight', {}, 3000);
  }, "html");
}
