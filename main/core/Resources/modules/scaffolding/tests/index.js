import {
  equal,
  propTypesOk,
  missingProps,
  invalidProps
} from './assert'

import {
  watchConsole,
  restoreConsole
} from './console'

export {
  renew,
  describeComponent,
  mountComponent,
  shallowComponent
} from './component'

export {mock as mockGlobals} from './mock'

export const ensure = {
  equal: equal,
  propTypesOk: propTypesOk,
  missingProps: missingProps,
  invalidProps: invalidProps
}

export const spyConsole = {
  watch: watchConsole,
  restore: restoreConsole
}
