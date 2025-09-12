# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-01-12

### Added
- Complete rewrite with modern PHP 8.3+ features
- Strict typing throughout the codebase
- PSR-3 compatible logging system
- Comprehensive middleware system
- Multiple authentication methods (Basic, Bearer, API Key)
- Retry mechanism with exponential backoff
- Rate limiting and throttling capabilities
- Response caching with TTL support
- Async request support
- Configuration management system
- Comprehensive test suite with PHPUnit
- Extensive documentation with examples
- CI/CD pipeline with GitHub Actions
- Code quality tools (PHPStan, PHPCS)
- File-based caching implementation
- Request/Response classes with proper typing
- Exception handling with detailed error information
- Support for all HTTP methods (GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS)
- cURL-based implementation with full feature support
- Response parsing (JSON, XML)
- Header management
- Query parameter handling
- Timeout and SSL configuration
- Redirect handling
- User agent customization

### Changed
- Complete API redesign for better usability
- Improved error handling and exception types
- Enhanced logging capabilities
- Better configuration management
- More flexible middleware system

### Removed
- Legacy API from version 1.x
- Old logging implementation
- Basic cURL wrapper functionality

### Security
- SSL verification enabled by default
- Secure authentication implementations
- Input validation and sanitization

## [1.2.0] - 2024-01-01

### Added
- Basic HTTP client functionality
- Support for multiple HTTP methods
- Simple logging system
- Cookie management
- Basic error handling

### Changed
- Improved documentation
- Better code organization

## [1.1.0] - 2023-12-01

### Added
- Initial release
- Basic HTTP request functionality
- Simple logging
- Cookie support

## [1.0.0] - 2023-11-01

### Added
- Initial project setup
- Basic HTTP client implementation
