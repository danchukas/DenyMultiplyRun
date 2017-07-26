Feature: Alert when lock application failed.
  For
  - more easy first use,
  - signaling to developer/admin in time,
  - more informational test/debug.

  Scenario: Lock application on clear system throw Exception
    Given a non existed file named '/var/proc/test.pid'
    When I run "DenyMultiplyRun::setPidFile('/var/proc/test.pid')"
    Then file '/var/proc/test.pid' should created with "PID current process"

  Scenario: Lock application after manual created file under another user than next runned.
    Given an existed file named '/etc/hosts' with no write access
    When I run "DenyMultiplyRun::setPidFile('/etc/hosts')"
    Then called method throw Exception.
