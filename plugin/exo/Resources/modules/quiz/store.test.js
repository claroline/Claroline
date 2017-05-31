import {ensure, mockTranslator} from '#/main/core/tests'
import {createStore} from './store'

describe('createStore', () => {
  beforeEach(mockTranslator)

  it('initializes the store with initial data and calls reducer', () => {
    const state = {
      resourceNode: {id: '1'},
      noServer: false,
      quiz: {id: '1'},
      steps: {},
      items: {}
    }
    const store = createStore(state)
    ensure.equal(store.getState(), {
      resourceNode: {id: '1'},
      noServer: false,
      testMode: false,
      currentRequests: 0,
      quiz: {id: '1'},
      steps: {},
      items: {},
      editor: {
        opened: false,
        saved: true,
        saving: false,
        validating: false,
        currentObject: {},
        openPanels: {
          quiz: false,
          step: {}
        }
      },
      currentStep: null,
      paper: {},
      answers: {},
      modal: {
        type: null,
        props: {},
        fading: false
      },
      viewMode: 'overview',
      papers: {
        papers: {},
        isFetched: false
      },
      correction: {}
    })
  })
})
