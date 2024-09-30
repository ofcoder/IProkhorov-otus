<?php
  /**
   * //.settings.php
   * 'connections' =>
   * array (
   * 'value' =>
   * array (
   * 'default' =>
   * array (
   * 'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
   * 'host' => 'localhost',
   * 'database' => '**********',
   * 'login' => '*********',
   * 'password' => '**********',
   * 'options' => 2.0,
   * ),
   * // База данных для логов
   * 'log_db' => [
   * 'className' => '\\Bitrix\\Main\\DB\\MysqliConnection',
   * 'host' => 'localhost',
   * 'database' => 'bitrix_log',
   * 'login' => '*********',
   * 'password' => '*********',
   * 'options' => 2.0
   * ],
   * ),
   * 'readonly' => true,
   * ),
   *
   *
   * 'exception_handling' =>
   * array (
   * 'value' =>
   * array (
   * 'debug' => false,
   * 'handled_errors_types' => E_ALL & ~E_NOTICE & ~E_DEPRECATED,
   * 'exception_errors_types' => E_ALL & ~E_NOTICE & ~E_DEPRECATED,
   * 'ignore_silence' => true,
   * 'assertion_throws_exception' => false,
   * 'assertion_error_type' => 256,
   * 'log' => [
   * 'class_name' => 'OfcoderDBExeptionHandlerLog', // Название своего класса
   * // Система ищет или в папке bitrix, или в local, то есть система сама подставит / перед
   * 'required_file' => 'local/php_interface/classes/Ofcoder/Diagnostic/OfcoderDBExeptionHandlerLog.php',
   * // Если БД недоступна, то хотя бы запишет в файл
   * 'settings' => [
   * 'file' => 'local/logs/bitrix.log',
   * 'log_size' => 1000000,
   * // Битрикс по умолчанию генерирует уйму, просто кучу исторических ошибок. Чтобы не забить, игнориуем один из типов
   * 'dont_show' => [\Bitrix\Main\Diag\ExceptionHandlerLog::LOW_PRIORITY_ERROR]
   * ],
   * ],
   * ),
   * 'readonly' => false,
   * ),
   */
  
  
  //Класс
  /**
	*-Создание экземпляра класса
	*-Создание таблицы, если ее не существует. Каждый месяц будет создаваться новая таблица
	*-Записать ошибку
	*-Если не удалось записать или нет соединение с БД, записать в файл, чтобы не потерят логи
  */
 
use Bitrix\Main\Diag\ExceptionHandlerFormatter;
use Bitrix\Main\Diag\ExceptionHandlerLog;

/**
 * @see \Bitrix\Main\Application::createExceptionHandlerLog
 */
class OfcoderDBExeptionHandlerLog extends ExceptionHandlerLog
{
  private string $table_name = 'error_log_';
  private $level;
  private \Bitrix\Main\Diag\FileExceptionHandlerLog $file_logger;
  private \Bitrix\Main\DB\MysqliConnection|\Bitrix\Main\DB\Connection $connection;
  
  private array $dont_show;
  
  /**
   * @param \Throwable $exception
   * @param int $logType
   */
  public function write($exception, $logType)
  {
    if (in_array($logType, $this->dont_show)) {
      return;
    }
    
    $log_type = $this::logTypeToString($logType);
    $text = ExceptionHandlerFormatter::format($exception, false, $this->level);
    
    try {
      $this->connection->add($this->getTableName(),
        ['message' => $exception->getMessage(), 'stack_trace' => $text, 'error_level' => $log_type, 'context' => json_encode(['uri' => $_SERVER['REQUEST_URI']])]);
    } catch (\Bitrix\Main\DB\SqlException $e) {
      $this->file_logger->write($exception, $logType);
      $this->file_logger->write($e, $logType);
    }
  }
  
  public function initialize(array $options)
  {
    try {
      $this->connection = \Bitrix\Main\Application::getConnection('log_db');
      
      $t = $this->getTableName();
      $exist = $this->connection->isTableExists($t);
      
      if (!$exist) {
        $this->createTable();
      }
      $this->level = $options['level'] ?? 0;
      $this->dont_show = $options['dont_show'] ?? [];
    } finally {
      $this->file_logger = new \Bitrix\Main\Diag\FileExceptionHandlerLog();
      $this->file_logger->initialize($options);
    }
  }
  
  public function getTableName(): string
  {
    return ($this->table_name . date('Y_m_01'));
  }
  
  protected function createTable()
  {
    $table_name = $this->getTableName();
    $sql = "CREATE TABLE `{$table_name}` (
	`id` INT(10) NOT NULL AUTO_INCREMENT,
	`error_level` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`message` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
	`stack_trace` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`context` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
;";
    
    $this->connection->queryExecute($sql);
  }
}