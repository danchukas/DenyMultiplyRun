Feature: Lock application
  Main functional.

  Scenario: Lock application on right sed system
    Given a non existed file as "$pid_file"
    When I run "DenyMultiplyRun::setPidFile($pid_file);"
    Then file "$pid_file" should created with "PID current process"

  Scenario: Lock application after crash
    Given an existed file named '/tmp/test.pid'
    When I run "DenyMultiplyRun::setPidFile('/tmp/test.pid')"
    Then file '/tmp/test.pid' should updated with "PID current process"
