import assert from 'assert'
import ResultService from './../../modules/results/results.service'

describe('Result service', () => {
  beforeEach(() => {
    global.window = {
      resultId: 1,
      resultMarks: [],
      workspaceUsers: []
    }
  })

  describe('constructor', () => {
    it('expects resource id, max, marks and users to be globally defined', () => {
      window = {}

      assert.throws(
        () => new ResultService({}, {}),
        /Expected resultId to be exposed in a window\.resultId variable/
      )

      window.resultId = 1;
      assert.throws(
        () => new ResultService({}, {}),
        /Expected resultMax to be exposed in a window\.resultMax variable/
      )

      window.resultMax = 10;
      assert.throws(
        () => new ResultService({}, {}),
        /Expected resultMarks to be exposed in a window\.resultMarks variable/
      )

      window.resultMarks = []
      assert.throws(
        () => new ResultService({}, {}),
        /Expected workspaceUsers to be exposed in a window\.workspaceUsers variable/
      )
    })
  })
})
