# Utility Objects
A few variations on the `stdClass` object.

`almostStd` Is very much like `stdClass`, but only the defined propeties of class can be set/get.

`almostStdImmutable` extends `almostStd` and removes ability to modify the property values after instantiation. Properties have to be set by passing stdClass object to constructor.

`almostStdValidatable` extends `almostStd` and adds a validation callback to get/set.

## Install
`composer require calderawp/object`

### Copyright 2016 CalderaWP LLC & Josh Pollock. Licensed under the terms of the GNU GPL version 2 or later. Please share with your neighbor.
