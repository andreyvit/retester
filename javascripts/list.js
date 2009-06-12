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
  
  $('form#new_test_form').submit(function() {
    $.post('/admin/tests/new', $(this).serialize(), {}, "script");
    return false;
  });
});

function deleteTest(testId, testName) {
  if(!confirm('Вы уверены, что хотите удалить «' + testName + '»?')) return false;
  $.post('/admin/tests/'+testId+'/delete', {}, function() {
    $('#test_'+testId).fadeOut('slow');
  });
}
  
function testCreated(testId) {
  window.location.href = '/admin/tests/' + testId + '/';
}
