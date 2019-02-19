import freeze from 'deep-freeze'

import {ensure} from '#/main/core/scaffolding/tests'
import {reducePapers} from './reducer'
import {PAPER_CURRENT} from './actions'

describe('Papers reducer', () => {
  it('returns an empty papers list by default', () => {
    const papers = reducePapers(undefined, {})
    ensure.equal(papers, {
      papers: {},
      isFetched: false
    })
  })

  it('updates current paper id', () => {
    const state = freeze({current: '1', papers: {}})
    const papers = reducePapers(state, {type: PAPER_CURRENT, id: '2'})
    ensure.equal(papers, {current: '2', papers: {}})
  })
})
