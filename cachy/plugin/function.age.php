<?php

function smarty_function_age($params, $smarty)
{ 
	$date = UTIL_DateTime::parseDate( $params['dateTime'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT );
        return UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
}
?>