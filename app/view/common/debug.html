<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>サイト名</title>
  <link rel="stylesheet" href="/css/normalize.css" type="text/css" media="all">
  <link rel="stylesheet" href="/css/debug.css" type="text/css" media="all">
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
</head>
<body>

  <h2>Debug</h2>

  <?php $isFirstLog = true; ?>
  <?php foreach($logs as $log): ?>
    <?php if($log['class']=='url'): ?>

      <?php if ($isFirstLog) : ?>
        <?php $isFirstLog = false; ?>
      <?php else: ?>
        </tbody></table>
      <?php endif; ?>

      <h2 class="debug-url"><?php echo $log['msg']; ?></h2>
      <pre class="debug-url-params"><?php var_dump($log['params']); ?></pre>
      <table class="debug-logs"><tbody>
        <tr>
          <th>time</th>
          <th>memory</th>
          <th>max</th>
          <th>info</th>
        </tr>

    <?php else: ?>

        <tr class="<?php echo $log['class']; ?>">
          <td class="time"><?php echo round($log['time'], 5); ?></td>
          <td class="memory">
            <?php echo $log['memory']; ?><br />
            <?php echo round($log['memory'] / 1024 / 1024, 3); ?>MB
          </td>
          <td class="memory">
            <?php echo $log['max_memory']; ?><br />
            <?php echo round($log['max_memory'] / 1024 / 1024, 3); ?>MB
          </td>
          <td>
            <p class="info"><?php echo $log['file']; ?>:<?php echo $log['line']; ?></p>
            <p class="log"><?php echo $log['msg']; ?></p>
            <?php if($log['params']): ?>
              <pre class="log"><?php var_dump($log['params']); ?></pre>
            <?php endif; ?>
          </td>
        </tr>

    <?php endif; ?>

  <?php endforeach; ?>
      </tbody></table>

  <?php \Fc2blog\Web\Session::start(); ?>
  <?php if(!empty($_SESSION)): ?>
    <h3 id="sys-debug-session">Session &gt;&gt;</h3>
    <pre style="display: none;"><?php var_dump($_SESSION); ?></pre>
  <?php endif; ?>

  <?php if(!empty($_COOKIE)): ?>
    <h3 id="sys-debug-cookie">Cookie &gt;&gt;</h3>
    <pre style="display: none;"><?php var_dump($_COOKIE); ?></pre>
  <?php endif; ?>

  <script>
  // 親のiframeの高さを自動拡張
  function debugIFrameSetting(){
    var pageHight = $(document).height() + 10;
    $('#sys-debug-iframe', parent.document).css({height:(pageHight)});
  }
  $(function(){
    debugIFrameSetting();
    $('#sys-debug-session, #sys-debug-cookie').on('click', function(){
      $(this).next().toggle();
      debugIFrameSetting();
    });
  });
  </script>

</body>
</html>
