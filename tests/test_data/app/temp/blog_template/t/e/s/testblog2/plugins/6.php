<div class="plugin-calender">
  <table summary="カレンダー" class="calender">
    <caption>
      <a href="<?php echo \Fc2blog\Web\Html::url($request, array('blog_id'=>$blog_id, 'action'=>'date', 'date'=>date('Ym', strtotime($prev_month_date)))); ?>"><?php echo date('m', strtotime($prev_month_date)); ?></a>
      | <?php echo date('Y', strtotime($now_date)); ?>/<?php echo date('m', strtotime($now_date)); ?> | 
      <a href="<?php echo \Fc2blog\Web\Html::url($request, array('blog_id'=>$blog_id, 'action'=>'date', 'date'=>date('Ym', strtotime($next_month_date)))); ?>"><?php echo date('m', strtotime($next_month_date)); ?></a>
    </caption>
    <tr>
      <th abbr="日曜日" scope="col" id="sun">日</th>
      <th abbr="月曜日" scope="col">月</th>
      <th abbr="火曜日" scope="col">火</th>
      <th abbr="水曜日" scope="col">水</th>
      <th abbr="木曜日" scope="col">木</th>
      <th abbr="金曜日" scope="col">金</th>
      <th abbr="土曜日" scope="col" id="sat">土</th>
    </tr>
    <?php if(!isset($t_calendars)) $t_calendars = \Fc2blog\Model\Model::load('Entries')->getTemplateCalendar($request, $blog_id, date('Y', strtotime($now_date)), date('m', strtotime($now_date))); ?><?php if (!empty($t_calendars)) foreach($t_calendars as $t_calendar) { ?>
    <tr>
      <td><?php if(isset($t_calendar[0])) echo $t_calendar[0]; ?></td>
      <td><?php if(isset($t_calendar[1])) echo $t_calendar[1]; ?></td>
      <td><?php if(isset($t_calendar[2])) echo $t_calendar[2]; ?></td>
      <td><?php if(isset($t_calendar[3])) echo $t_calendar[3]; ?></td>
      <td><?php if(isset($t_calendar[4])) echo $t_calendar[4]; ?></td>
      <td><?php if(isset($t_calendar[5])) echo $t_calendar[5]; ?></td>
      <td><?php if(isset($t_calendar[6])) echo $t_calendar[6]; ?></td>
    </tr>
    <?php } ?>
  </table>
</div>