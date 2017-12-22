import {
  VIEW_EDITOR,
  VIEW_OVERVIEW,
  VIEW_PLAYER,
  VIEW_ATTEMPT_END,
  VIEW_PAPERS,
  VIEW_PAPER,
  VIEW_STATISTICS,
  VIEW_CORRECTION_QUESTIONS,
  VIEW_CORRECTION_ANSWERS
} from './enums'

import {Overview}   from './overview/overview.jsx'
import {Player}     from './player/components/player.jsx'
import {AttemptEnd} from './player/components/attempt-end.jsx'
import {Editor}     from './editor/components/editor.jsx'
import {Papers}     from './papers/components/papers.jsx'
import {Paper}      from './papers/components/paper.jsx'
import {Questions}  from './correction/components/questions.jsx'
import {Answers}    from './correction/components/answers.jsx'
import {Statistics}    from './statistics/components/statistics.jsx'

// Map components to defined routes
export const viewComponents = {
  [VIEW_OVERVIEW]: Overview,
  [VIEW_EDITOR]: Editor,
  [VIEW_PLAYER]: Player,
  [VIEW_ATTEMPT_END]: AttemptEnd,
  [VIEW_PAPERS]: Papers,
  [VIEW_PAPER]: Paper,
  [VIEW_CORRECTION_QUESTIONS]: Questions,
  [VIEW_CORRECTION_ANSWERS]: Answers,
  [VIEW_STATISTICS]: Statistics
}
