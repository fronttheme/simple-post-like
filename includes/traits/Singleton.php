<?php

namespace FrontTheme\SimplePostLike\Traits;

use Exception;

defined('ABSPATH') || exit;

trait Singleton {
  private static array $instances = [];

  public static function instance() {
    $class = static::class; // Get the actual calling class

    if (!isset(self::$instances[$class])) {
      self::$instances[$class] = new static(); // Create instance of calling class
    }

    return self::$instances[$class];
  }

  private function __construct() {
    $this->init(); // Template method pattern
  }

  /**
   * Override this in classes instead of __construct
   */
  protected function init(): void {}

  private function __clone() {}

  /**
   * @throws Exception
   */
  public function __wakeup(): void {
    throw new Exception("Cannot unserialize singleton");
  }
}