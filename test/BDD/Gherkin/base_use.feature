Feature: Lock application
  Main functional.

  Scenario: Lock application with right install
    Given a non existed "<pid-file>"
    When I run
      """
        DanchukAS\DenyMultiplyRun\DenyMultiplyRun::setPidFile(<pid-file>);
      """
    Then file "<pid-file>" should created with "<current PID>"

  Scenario: Lock application after crash
    Given an existed file named "<pid-file>" with "<old no exist PID>"
    When I run
      """
        DanchukAS\DenyMultiplyRun\DenyMultiplyRun::setPidFile(<pid-file>);
      """
    Then file "<pid-file>" should updated with "<current PID>"
