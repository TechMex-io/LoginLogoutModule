# LoginLogoutModule
A module for Processwire CMS to add login/logout links in a template

## Simple Version (simple)
This is the preferred version. To be used in conjunction with `RoleBasedRedirect` module if you want to redirect users based on their role.

### Install
```
$ cd site/modules
$ mkdir LoginLogoutModule
$ cd LoginLogoutModule
$ git clone git@github.com:TechMex-io/LoginLogoutModule.git .
```

Install the module via the `Modules` section of the Processwire admin.

### Usage
In a template:
`<?= $modules->get('LoginLogoutModule')->getLoginLogoutLink() ?>`


---

## Configurable Version (main)

### Install

```
$ cd site/modules
$ mkdir LoginLogoutModule
$ cd LoginLogoutModule
$ git clone git@github.com:TechMex-io/LoginLogoutModule.git .
$ git checkout simple
```

Install the module via the `Modules` section of the Processwire admin.

### Configuration
In the module's settings, add the key value pairs of `userrole=/URL/`, for example:
```
admin=/clients/
```
with this, any user with a role of `admin` will be redirected to `/clients/`.

### Usage
In a template:
`<?= $modules->get('LoginLogoutModule')->getLoginLogoutLink() ?>`
