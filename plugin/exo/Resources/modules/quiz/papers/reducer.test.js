import freeze from 'deep-freeze'

import {ensure, mockTranslator} from '#/main/core/tests'
import {reducePapers} from './reducer'
import {PAPERS_INIT, PAPER_CURRENT} from './actions'

describe('Papers reducer', () => {
  before(mockTranslator)

  it('returns an empty papers list by default', () => {
    const papers = reducePapers(undefined, {})
    ensure.equal(papers, {
      papers: {},
      isFetched: false
    })
  })

  it('sets papers on init', () => {
    const papers = reducePapers({}, {type: PAPERS_INIT, papers: {id: 'PAPERS'}})
    ensure.equal(papers.papers, {id: 'PAPERS'})
  })

  it('updates current paper id', () => {
    const state = freeze({current: '1', papers: {}})
    const papers = reducePapers(state, {type: PAPER_CURRENT, id: '2'})
    ensure.equal(papers, {current: '2', papers: {}})
  })
})
