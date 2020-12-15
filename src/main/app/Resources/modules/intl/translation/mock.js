/* global sinon */

import {Translator} from './translator'

/**
 * Mock translator for tests purposes.
 */
function mock() {
  sinon
    .stub(Translator, 'trans')
    .callsFake(msg => msg)

  sinon
    .stub(Translator, 'transChoice')
    .callsFake(msg => msg)
}

export {
  mock
}
