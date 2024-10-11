<?php
  
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