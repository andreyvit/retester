jQuery(function($) {
  $('#new_test').click(function() {
    if ($('#new_test_editor').is(':visible'))
      $('#new_test_editor').slideUp();
    else {
      $('#new_test_editor').slideDown();
      $('#new_test_editor input[name=name]').focus();
    }
    return false;
  });
  
  $('#new_test_editor form').submit(function() {
    $.post('test-editor.php', $(this).serialize(), {}, "script");
    return false;
  });
});

function deleteTest(testId, testName) {
  if(!confirm('Вы уверены, что хотите удалить «' + testName + '»?')) return false;
  $.post('test-delete.php', {'test_id': testId}, function() {
    $('#test_'+testId).fadeOut('slow');
  });
}
  
function testCreated(testId) {
  window.location.href = 'test.php?id=' + testId;
}
