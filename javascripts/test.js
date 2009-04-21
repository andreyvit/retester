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
                if ($(this).find('input[name$=text]').val()) {
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
            var data = $(this).find('.question input[name*=order]').serialize()
              + '&' + $('#test_id').serialize();
            $.post('reorder-questions.php', data);
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
    var html = "<p style='display: none' class='editor' id='" + editorId + "'></p>";
    $(this).after(html);
    $("#" + editorId).load("question_editor.php?test_id=" + test_id + "&question_id=" + qid, {}, function() {
      $("#" + editorId).slideDown("fast");
    });
  });
  
  $('form.question_editor').live('submit', function() {
    var data = $(this).serialize();
    $.post("question_editor.php?editor_id=" + this.parentNode.id, data, {}, "script");
    return false;
  });
  
  $('.cancel_editor').live('click', function() {
    closeEditor($(this).closest('.editor'));
    return false;
  });
  
  $('.remove_answer').live('click', function() {
    $(this).closest('li')
      .fadeOut("slow")
      .find('input[name$=text]').val('');
    return false;
  });
  $('.remove_question').live('click', function() {
    var q = $(this).closest('.question');
    var id = q.attr('id').split('_')[1];
    $.post('delete-question.php', {'question_id': id}, function() {
      q.fadeOut("slow");
    });
    return false;
  });
});

function closeEditor(editor) {
  $(editor).slideUp("fast", removeFromDom);
}

function questionNotFound(editor_id, question_id) {
  alert('Извините, этот вопрос уже удален.');
  closeEditor($('#' + editor_id));
  $('question_' + question_id).fadeOut();
}

function questionSaved(editor_id, question_id, inserted) {
  closeEditor($('#' + editor_id));
  $.get('question.php?question_id=' + question_id, {}, function(text) {
    if (inserted) {
      $('#question_new').before(text);
    } else {
      $('#question_'+question_id).replaceWith(text);
    }
    $('#question_' + question_id).effect('highlight', {}, 3000);
  }, "html");
}
