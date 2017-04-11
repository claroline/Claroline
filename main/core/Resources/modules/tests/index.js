/* eslint no-console: "off" */

import {
  assertEqual,
  assertPropTypesOk,
  assertMissingProps,
  assertInvalidProps
} from './assert'
import {watchConsole, restoreConsole} from './console'

// exposes mock
export {
  mockRouting,
  mockTinymce,
  mockTranslator
} from './mock'

export const ensure = {
  equal: assertEqual,
  propTypesOk: assertPropTypesOk,
  missingProps: assertMissingProps,
  invalidProps: assertInvalidProps
}

export const spyConsole = {
  watch: watchConsole,
  restore: restoreConsole
}

// assign a random name to a component, ensuring it isn't cached and thus prop
// types are always checked
// (see https://github.com/facebook/react/issues/7047#issuecomment-228614964)
export function renew(component, name) {
  component.displayName = `${name}-${Math.random().toString()}`
}
