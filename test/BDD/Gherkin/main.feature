Feature: realize spin block using a file.

  Scenario: Failed multiply run php-script.
    Given access for create/rewrite "<pid-file>"
    And runned "<php-script>" which contains:
        """
          DanchukAS\DenyMultiplyRun\DenyMultiplyRun::setPidFile(<pid-file>);
        """
    When try run <some-php-script> <any> times during running already runned "<php-script>"
      | any | some-php-script      |
      | 1   | <php-script>         |
      | 1   | <similar-php-script> |
      | 2   | <php-script>         |
      | 3   | <similar-php-script> |
    Then all attempts are failed
    And given runned "<php-script>" work regardless of subsequent attempts






