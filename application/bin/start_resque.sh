#!/bin/sh
#Provided by @soeasy

WEB_DIR=$(dirname "$PWD")
PHP_PATH="/usr/bin/env php"
PHP_FILE_PATH="${WEB_DIR}/bin/resque.php"
LOG_PATH="${WEB_DIR}/logs"
CACHE_PATH="${WEB_DIR}/cache"
curdate=$(TZ='Asia/Shanghai' date +%Y-%m-%d' '%H:%M:%S)
ENVIRON=$(/usr/bin/env php --ri yaf | grep yaf.environ | awk '{print $5}')
curmonth=$(date +%Y-%m)
LOG_FILE_PATH="${LOG_PATH}/resque.log"
APPLICATION_INI_PATH="${WEB_DIR}/conf/application.ini"
RESQUE_CONFIG_NAME=$(awk -F '=' '/\['${ENVIRON}' \: 'common'\]/{a=1}a==1&&$1~/'resque.config'/{print $2;exit}' ${APPLICATION_INI_PATH} | sed 's/\"//g;s/[[:space:]]//g;s/\r//g')
RESQUE_CONFIG="${WEB_DIR}/conf/"${RESQUE_CONFIG_NAME}
PID_FILE="${CACHE_PATH}/default.pid"
PID=$(cat ${PID_FILE} 2> /dev/null)
if [ ! -f "$RESQUE_CONFIG" ]; then
  $PHP_PATH $PHP_FILE_PATH > /dev/null 2>&1
fi

start() {
  echo   "Starting php-resque..."
  echo   "Start php-resque at: ${curdate} " >> "${LOG_FILE_PATH}"
  nohup   $PHP_PATH $PHP_FILE_PATH --config=$RESQUE_CONFIG worker:start >> $LOG_FILE_PATH  2>&1 &
}

stop() {
  echo   "Stopping php-resque..."
  echo   "Stop php-resque at: ${curdate} " >> "${LOG_FILE_PATH}"
  nohup   $PHP_PATH $PHP_FILE_PATH --config=$RESQUE_CONFIG worker:stop $PID >> $LOG_FILE_PATH  2>&1 &
}

stopAll() {
  echo   "Stopping php-resque..."
  echo   "Stop php-resque at: ${curdate} " >> "${LOG_FILE_PATH}"
  nohup   $PHP_PATH $PHP_FILE_PATH --config=$RESQUE_CONFIG worker:stop >> $LOG_FILE_PATH  2>&1 &
}

restart() {
  echo   "Restarting php-resque..."
  echo   "Restart php-resque at: ${curdate} " >> "${LOG_FILE_PATH}"
  nohup   $PHP_PATH $PHP_FILE_PATH --config=$RESQUE_CONFIG worker:restart $PID >> $LOG_FILE_PATH  2>&1 &
}

case "$1" in
  start)
    start
    ;;
  stop)
    stop
    ;;
  stopall)
    stopAll
    ;;
  restart)
    restart
    ;;
  *)
    echo     "Usage: $0 {start|stop|stopall|restart}"
    exit     1
    ;;
esac

exit 0
