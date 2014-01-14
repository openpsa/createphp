Changelog
=========

* **2014-01-13**: Moved workflows from Manager to RestService. If you used
  the Manager before, please update your code to use the RestService.
  Before:
  ```
    $manager->registerWorkflow(...)
  ```
  After:
  ```
    $manager->getRestHandler()->registerWorkflow(...)
  ```

* **2013-11-18**: Added mapper for Doctrine ORM. Removed
  AbstractDoctrineMapper::createSubject as it contained invalid assumptions.
