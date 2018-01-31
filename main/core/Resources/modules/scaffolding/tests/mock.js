import {mock as mockAsset} from '#/main/core/scaffolding/asset/mock'
import {mock as mockRouter} from '#/main/core/api/router/mock'
import {mock as mockTranslation} from '#/main/core/translation/mock'

let mocked = false

/**
 * Mocks global Claroline app components.
 */
function mock() {
  if (!mocked) {
    mockAsset()
    mockTranslation()
    mockRouter()

    // mock tinyMCE
    window.tinymce = {
      get: () => ({
        on: () => {},
        setContent: () => {},
        destroy: () => {}
      })
    }

    mocked = true
  }
}

export {
  mock
}
