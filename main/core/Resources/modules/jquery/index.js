/**
 * This module simply makes the global jQuery instance (injected via a script
 * tag for compatibily reasons) available to other modules. It is aliased in the
 * webpack config so that importing "jquery" will amount to importing this
 * module.
 *
 * Obviously this is temporary and should go as soon as a global jQuery instance
 * isn't necessary anymore (i.e. when it's only required via module imports).
 */

export default window.$
