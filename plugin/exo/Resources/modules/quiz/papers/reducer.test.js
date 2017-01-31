import freeze from 'deep-freeze'
import {assertEqual} from './../../utils/test'
import {reducePapers} from './reducer'
import {PAPERS_INIT, PAPER_CURRENT} from './actions'

describe('Papers reducer', () => {
  it('returns an empty papers object by default', () => {
    const papers = reducePapers(undefined, {})
    assertEqual(papers, {})
  })

  it('sets papers on init', () => {
    const papers = reducePapers({}, {type: PAPERS_INIT, papers: 'PAPERS'})
    assertEqual(papers.papers, 'PAPERS')
  })

  it('updates current paper id', () => {
    const state = freeze({current: '1', papers: {}})
    const papers = reducePapers(state, {type: PAPER_CURRENT, id: '2'})
    assertEqual(papers, {current: '2', papers: {}})
  })
})
