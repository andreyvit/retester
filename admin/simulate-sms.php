<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title>Отладка СМС</title>
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
  <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js"></script>
</head>
<body>
  
  <form action="../x-sms-a1.php" method="GET">
    <table>
      <tr>
        <td><label for="user_id">Телефон абонента:</label></td>
        <td><input id="user_id" name="user_id" value="71111111111" /></td>
      </tr>
      <tr>
        <td><label for="user_id">Короткий номер:</label></td>
        <td><input id="user_id" name="user_id" value="1121" /></td>
      </tr>
      <tr>
        <td><label for="cost">Стоимость:</label></td>
        <td><input id="cost" name="cost" value="4.61134054658" /></td>
      </tr>
      <tr>
        <td><label for="msg">Сообщение:</label></td>
        <td><input id="msg" name="msg" value="+result QWER" /></td>
      </tr>
      <tr>
        <td><label for="operator_id">ID оператора:</label></td>
        <td><input id="operator_id" name="operator_id" value="299" /></td>
      </tr>
      <tr>
        <td><label for="ran">Надежность:</label></td>
        <td><input id="ran" name="ran" value="5" /></td>
      </tr>
    </table>
    <p><input type="submit" value="Отправить" /></p>
  </form>
  
  <div>
    <iframe id="target" name="smstarget">
    </iframe>
  </div>
  
  <script>
    jQuery(function($) {
      $('form').submit(function(event) {
        event.preventDefault();
        $('#target').attr('src', this.action + '?' + $(this).serialize()).effect('highlight');
        return false;
      });
    });
  </script>
</body>
