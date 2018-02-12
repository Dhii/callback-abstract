# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD

## [0.1-alpha2] - 2018-02-12
### Fixed
- Exception type throw by `InvokeCallableCapableTrait#_invokeCallable()` is now properly documented to be
`InvocationExceptionInterface` instead of `RootException`.

### Changed
- `_setArgs()` and `_invokeCallback()` now normalize the args list, instead of manually validating it.
- `_setArgs()`, `_invokeCallback()`, and `_invokeCallable()` now accept `stdClass` as arg list.

## [0.1-alpha1] - 2018-02-12
Initial version.
