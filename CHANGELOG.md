# Change Log

## [0.10.0] - 2022-02-24
### Added
- Added `queue:add-job` command to add a job to a queue
- Added `-n` option to `queue:process` to allow a specified number of jobs to be processed

### Changed
- N/A

### Fixed
- N/A

## [0.9.1] - 2022-02-24
### Added
- N/A

### Changed
- Support both PSR Log 2.0 and 3.0

### Fixed
- N/A

## [0.9.0] - 2022-02-23
### Added
- Added support for PSR-3 loggers

### Changed
- Some commands are now logger aware (`cron:force`, `cron:process`, `queue:process`, `manager:run`)
- Output of some console lines have changed to better match the logger

### Fixed
- N/A

## [0.8.0] - 2022-02-17

### Added
- Added `cron:info` command to view what crons are registered

### Changed
- Changed autoloading for most of the commands

### Fixed
- N/A


## [0.7.1] - 2022-02-14

### Added
- N/A

### Changed
- N/A

### Fixed
- Fixed manager config keys in default config

## [0.7.0] - 2022-02-14

### Added
- Configuration section for Manager

### Changed
- Manager now limits itself to a configurable number of workers to avoid resource exhaustion
- A default config is now generated that contains a `manager` and `pheanstalk` section

### Fixed
- N/A

## [0.6.0] - 2022-02-14

### Added
- Custom Commands can now be registered with `custom_commands` array in config file

### Changed
- N/A

### Fixed
- N/A

## [0.5.1] - 2022-02-11

### Added
- N/A
### Changed
- Cron Force now outputs if it cannot find the specified name

### Fixed
- Cron Force job has proper DI set

## [0.5.0] - 2022-02-11

### Added
- Cron jobs can now be forced with `cron:force-process <name>`

### Changed
- N/A

### Fixed
- N/A

## [0.4.0] - 2022-02-06

### Added
- Cron jobs can now be passed `options` to their `__invoke()` method, which are spread (`...`) as named arguments

### Changed
- N/A

### Fixed
- N/A
## [0.3.0] - 2022-01-25

### Added
- Added the ability to see condensed stats acrossed all queues

### Changed
- N/A

### Fixed
- N/A

## [0.2.0] - 2022-01-24

### Added
- Added the ability to clear all jobs from a queue

### Changed
- N/A

### Fixed
- N/A

## [0.1.0] - 2022-01-21

The initial release! All 0.x release may break backwards compatibility and are intended to early release testing only.

### Added
- Added ability to get status on queues
- Added ability to process cron jobs
- Added ability to process individual queues
- Added ability to peek at upcoming job in a queue
- Added main manager runner and basic logic

### Changed
- N/A

### Fixed
- N/A
