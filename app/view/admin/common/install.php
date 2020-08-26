<header><h2><?php echo __('Environment Check'); ?></h2></header>
<style>
ul.check > li
{
  border: 1px solid #999;
  margin: 3px;
  padding: 3px;
}
ul.check > li.ok
{
  border: 1px solid #090;
  background-color: #cfc;
}
ul.check > li.ng
{
  border: 1px solid #f66;
  background-color: #fcc;
}
  ul.check > li.ng p.ng
  {
    padding: 3px;
  }
ul.check > li.warning
{
  border: 1px solid #666;
  background-color: #ccc;
}
  ul.check > li hr
  {
    border: 1px solid #333;
    border-bottom: 1px solid #aaa;
    background-color: #333;
    margin: 0;
  }
table#db
{
  width: 400px;
  margin: 5px auto;
}
  #db th
  {
    width: 150px;
  }
</style>
<ul class="check">
  <?php //$is_write_temp = is_writable(\Fc2blog\Config::get('TEMP_DIR') . '.'); ?>
  <li class="<?php echo $is_write_temp ? 'ok' : 'ng'; ?>">
    <?php echo __('Write access to the temporary directory'); ?> . . .
    <?php if ($is_write_temp): ?>
      OK
    <?php else: ?>
      <span style="color: red;">NG!</span>
      <hr />
      <p class="ng">[<?php echo \Fc2blog\Config::get('TEMP_DIR'); ?>]<br /><?php echo __('Please perform a writable set for the above folder'); ?></p>
    <?php endif; ?>
  </li>

  <li class="<?php echo $is_write_upload ? 'ok' : 'ng'; ?>">
    <?php echo __('Write access to the upload directory'); ?> . . .
    <?php if ($is_write_upload): ?>
      OK
    <?php else: ?>
      <span style="color: red;">NG!</span>
      <hr />
      <p class="ng">[<?php echo \Fc2blog\Config::get('WWW_UPLOAD_DIR'); ?>]<br /><?php echo __('Please perform a writable set for the above folder'); ?></p>
    <?php endif; ?>
  </li>

  <?php $is_db_connect_lib = defined('DB_CONNECT_LIB'); ?>
  <li class="<?php echo $is_db_connect_lib ? 'ok' : 'ng'; ?>">
    <?php echo __('Check connection library with MySQL'); ?> . . .
    <?php if ($is_db_connect_lib): ?>
      OK
    <?php else: ?>
      <span style="color: red;">NG!</span>
      <hr />
      <p class="ng"><?php echo __('Please enable pdo or mysqli'); ?></p>
    <?php endif; ?>
  </li>

  <li class="<?php echo $is_connect ? 'ok' : 'ng'; ?>">
    <?php echo __('Check connection to the MySQL'); ?> . . .
    <?php if ($is_connect): ?>
      OK
    <?php else: ?>
      <span style="color: red;">NG!</span>
      <hr />
      <p class="ng">
        <?php echo __('Can not connect to DB'); ?><br />
        <?php echo __('Please check and there is no problem in the following setting'); ?><br />
        <?php echo __('Please change each item of config.php If you are different'); ?>
        <pre class="db"><?php echo $connect_message; ?></pre>
      </p>
    <?php endif; ?>
    <table id="db">
      <tr>
        <th><?php echo __('Host name'); ?></th>
        <td><?php echo DB_HOST; ?></td>
      </tr>
      <tr>
        <th><?php echo __('User name'); ?></th>
        <td><?php echo DB_USER; ?></td>
      </tr>
      <tr>
        <th><?php echo __('Password'); ?></th>
        <td><?php echo DB_PASSWORD; ?></td>
      </tr>
      <tr>
        <th><?php echo __('Database name'); ?></th>
        <td><?php echo DB_DATABASE; ?></td>
      </tr>
    </table>
  </li>

  <?php if ($is_connect): ?>
  <li class="<?php echo $is_character ? 'ok' : 'ng'; ?>">
    <?php echo __('Character code check of MySQL'); ?> . . .
    <?php if ($is_character): ?>
      OK
    <?php else: ?>
      <span style="color: red;">NG!</span>
      <hr />
      <p class="ng">
        <?php echo __('You can not use the character code of UTF8MB4 because the version of MySQL is older'); ?><br />
        <?php echo __('Please change to UTF8 DB_CHARSET'); ?><br />
        <?php echo __('Example'); ?>) define('DB_CHARSET',  'UTF8');
      </p>
    <?php endif; ?>
  </li>
  <?php endif; ?>

  <li class="<?php echo $is_domain ? 'ok' : 'ng'; ?>">
    <?php echo __('Check the configuration of the domain'); ?> . . .
    <?php if ($is_domain): ?>
      OK
    <?php else: ?>
      <span style="color: red;">NG!</span>
      <hr />
      <p class="ng">
        <?php echo __('Domain is set to the current domain'); ?><br />
        <?php echo __('Please change to the appropriate domain'); ?><br />
        <?php echo __('Example'); ?>) <?php echo $_SERVER["SERVER_NAME"]; ?>
      </p>
    <?php endif; ?>
  </li>

  <li class="<?php echo $is_salt ? 'ok' : 'ng'; ?>">
    <?php echo __('Check the configuration of the salt value'); ?> . . .
    <?php if ($is_salt): ?>
      OK
    <?php else: ?>
      <span style="color: red;">NG!</span>
      <hr />
      <p class="ng">
        <?php echo __('Salt value for password check (random character string) is still in the initial string'); ?><br />
        <?php echo __('Please change to a string of alphanumeric which is not easily predicted'); ?><br />
        <?php echo __('Example'); ?>) <?php echo \Fc2blog\App::genRandomStringAlphaNum(32); ?>
      </p>
    <?php endif; ?>
  </li>

  <li class="<?php echo $is_gd ? 'ok' : 'warning'; ?>">
    <?php echo __('Check the configuration of the GD library'); ?> . . .
    <?php if ($is_gd): ?>
      OK
    <?php else: ?>
      <span style="color: red;">NG!</span>
      <hr />
      <p class="ng">
        <?php echo __('GD library does not have installed'); ?><br />
        <?php echo __('Though it is possible to proceed with the installation of the blog'); ?><br />
        <?php echo __('Until you install the GD library'); ?><br />
        <?php echo __('You can not use the ability to create a thumbnail image'); ?>
      </p>
    <?php endif; ?>
  </li>
</ul>

<?php if ($is_all_ok): ?>
  <form>
    <input type="hidden" name="state" value="1" />
    <p class="form-button">
      <input type="submit" value="<?php echo __('Install'); ?>" />
    </p>
  </form>
<?php endif; ?>

