Feature: realize spin block using a file.

  Scenario:
    Given access for create/rewrite "<pid-file>"
    And "<php-script>" with:
        """
          DanchukAS\DenyMultiplyRun\DenyMultiplyRun::setPidFile(<pid-file>);
          <do-something>
        """
    And runned "<php-script>"
    When I try multiply run <php-script> <any> times
  during running already runned "<php-script>"
    Then all attempts are failed
    And given runned "<php-script>" work regardless of subsequent attempts

  Examples
  | any |     php-script       |
  |  1  | <same-php-script>    |
  |  1  | <similar-php-script> |
  |  2  | <same-php-script>    |
  |  3  | <similar-php-script> |


