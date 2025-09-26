# UmanIT Coding Standard

A custom ruleset for PHP_CodeSniffer including:

* Default PSR12 compliance.
* Prohibit usage of certain PHP functions.
* A bunch of rules from `slevomat/coding-standard`.
* A custom rule that forces usage of `@todo` annotation and forbid other ones like `TODO`, `FIXME` or `XXX`.

### Usage

Edit your `phpcs.xml` file to include the following:

```xml

<rule ref="UmanITCodingStandard" />
```
