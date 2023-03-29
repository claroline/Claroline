import {PropTypes as T} from 'prop-types'

import {User as UserType} from '#/main/community/prop-types'

const Sco = {
  propTypes: {
    id: T.string.isRequired,
    scorm: T.shape({
      id: T.string
    }),
    parent: T.shape({
      id: T.string
    }),
    children: T.array,
    data: T.shape({
      entryUrl: T.string,
      identifier: T.string,
      title: T.string,
      visible: T.bool,
      parameters: T.string,
      launchData: T.string,
      maxTimeAllowed: T.string,
      timeLimitAction: T.string,
      block: T.bool,
      scoreToPassInt: T.number,
      scoreToPassDecimal: T.number,
      scoreToPass: T.number,
      completionThreshold: T.number,
      prerequisites: T.string
    })
  }
}

const Scorm = {
  propTypes: {
    id: T.string.isRequired,
    version: T.string.isRequired,
    hashName: T.string.isRequired,
    ratio: T.number,
    scos: T.arrayOf(T.shape(Sco.propTypes))
  }
}

const ScoTracking = {
  propTypes: {
    id: T.string.isRequired,
    sco: T.shape(Sco.propTypes),
    user: T.shape(UserType.propTypes),
    scoreRaw: T.number,
    scoreMin: T.number,
    scoreMax: T.number,
    scoreScaled: T.number,
    lessonStatus: T.string,
    completionStatus: T.string,
    sessionTime: T.number,
    totalTime: T.string,
    totalTimeInt: T.number,
    totalTimeString: T.string,
    entry: T.string,
    suspendData: T.string,
    credit: T.string,
    exitMode: T.string,
    lessonLocation: T.string,
    lessonMode: T.string,
    bestScoreRaw: T.number,
    bestLessonStatus: T.string,
    isLocked: T.boolean,
    details: T.object,
    latestDate: T.string
  }
}

export {
  Sco,
  Scorm,
  ScoTracking
}