/* global sinon */

import {Asset} from './asset'

/**
 * Mock asset accessor for tests purposes.
 */
function mock() {
  sinon
    .stub(Asset, 'path')
    .callsFake(assetName => `${assetName}`) // todo find correct path
}

export {
  mock
}
