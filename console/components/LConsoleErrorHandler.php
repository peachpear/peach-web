<?php
namespace console\components;

use common\components\LException;
use Yii;
use yii\console\ErrorHandler;

/**
 * Class LConsoleErrorHandler
 * @package console\components
 */
class LConsoleErrorHandler extends ErrorHandler
{
    public function handleException( $exception )
    {
        $data = $this->formatException( $exception );
        $this->logException( $exception );

        if ( YII_DEBUG ) {
            throw $exception;
        }
    }

    public function handleError($code, $message, $file, $line)
    {
        $exception =  new \ErrorException($message, $code, 1, $file, $line);
        $this->logException($exception);
    }

    public function handleFatalError()
    {
        $error = error_get_last();
        if (LException::isFatalError($error)) {
            $exception = new \ErrorException($error['message'], 500, $error['type'], $error['file'], $error['line']);
            $this->exception = $exception;
//            $this->logException($exception);

            if ($this->discardExistingOutput) {
                $this->clearOutput();
            }
            // need to explicitly flush logs because exit() next will terminate the app immediately
            Yii::getLogger()->flush(true);
            $this->handleException($exception);
        }
    }

    protected function formatException($exception)
    {
        $fileName = $exception->getFile();
        $errorLine = $exception->getLine();

        $trace = $exception->getTrace();

        foreach ($trace as $i => $t)
        {
            if (!isset($t['file'])) {
                $trace[$i]['file'] = 'unknown';
            }

            if (!isset($t['line'])) {
                $trace[$i]['line'] = 0;
            }

            if (!isset($t['function'])) {
                $trace[$i]['function'] = 'unknown';
            }

            unset($trace[$i]['object']);
        }

        return array(
            'type' => get_class($exception),
            'errorCode' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $fileName,
            'line' => $errorLine,
            'trace' => $exception->getTraceAsString(),
//            'traces' => $trace,
        );
    }

    public function renderException($exception)
    {
        $this->handleException($exception);
    }
}