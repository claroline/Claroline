import {PropTypes as T} from 'prop-types'

import {Criterion} from '#/plugin/drop-zone/data/criteria/prop-types'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

const DropzoneType = {
  propTypes: {
    id: T.string.isRequired,
    instruction: T.string,
    parameters: T.shape({
      reviewType: T.oneOf(
        Object.keys(constants.REVIEW_TYPES)
      ).isRequired,
      dropType: T.oneOf(
        Object.keys(constants.DROP_TYPES)
      ).isRequired,

      /**
       * The list of allowed documents.
       */
      documents: T.arrayOf(
        T.oneOf(Object.keys(constants.DOCUMENT_TYPES))
      ).isRequired,
      autoCloseDropsAtDropEndDate: T.bool.isRequired,
      commentInCorrectionEnabled: T.bool.isRequired,
      commentInCorrectionForced: T.bool.isRequired,
      correctionDenialEnabled: T.bool.isRequired,
      criteriaEnabled: T.bool.isRequired,
      criteriaTotal: T.number.isRequired,

      expectedCorrectionTotal: T.number.isRequired,
      scoreMax: T.number.isRequired,
      scoreToPass: T.number.isRequired,
      revisionEnabled: T.bool.isRequired
    }).isRequired,
    display: T.shape({
      correctionInstruction: T.string,
      displayCorrectionsToLearners: T.bool.isRequired,
      showFeedback: T.bool.isRequired,
      showScore: T.bool.isRequired,
      correctorDisplayed: T.bool.isRequired,
      failMessage: T.string,
      successMessage: T.string
    }).isRequired,
    planning: T.shape({
      type: T.oneOf(
        Object.keys(constants.PLANNING_TYPES)
      ).isRequired,
      state: T.oneOf(
        Object.keys(constants.PLANNING_STATES.all)
      ),
      // drop date range
      drop: T.arrayOf(T.string),
      // review date range
      review: T.arrayOf(T.string)
    }).isRequired,
    notifications: T.shape({
      actions: T.arrayOf(T.string),
      enabled: T.bool.isRequired
    }).isRequired,
    criteria: T.arrayOf(
      T.shape(Criterion.propTypes)
    )
  }
}

const DropzoneToolDocumentType = {
  propTypes: {
    id: T.string.isRequired,
    document: T.string.isRequired,
    tool: T.string.isRequired,
    data: T.shape({
      idDocument: T.string,
      reportUrl: T.string
    })
  }
}

const Comment = {
  propTypes: {
    id: T.string.isRequired,
    content: T.string.isRequired,
    user: T.shape({
      autoId: T.number.isRequired,
      id: T.string.isRequired,
      username: T.string.isRequired,
      firstName: T.string.isRequired,
      lastName: T.string.isRequired
    }),
    creationDate: T.string,
    editionDate: T.string
  }
}

const DocumentType = {
  propTypes: {
    id: T.string.isRequired,
    type: T.oneOf(
      Object.keys(constants.DOCUMENT_TYPES)
    ).isRequired,
    drop: T.string.isRequired,
    user: T.shape({
      autoId: T.number.isRequired,
      id: T.string.isRequired,
      username: T.string.isRequired,
      firstName: T.string.isRequired,
      lastName: T.string.isRequired
    }),
    dropDate: T.string,
    toolDocuments: T.arrayOf(
      T.shape(DropzoneToolDocumentType.propTypes)
    ),
    revision: T.shape({
      id: T.string.isRequired
    })
  }
}

const GradeType = {
  propTypes: {
    id: T.string.isRequired,
    value: T.number,
    correction: T.string.isRequired,
    criterion: T.string.isRequired
  }
}

const CorrectionType = {
  propTypes: {
    id: T.string.isRequired,
    drop: T.string.isRequired,
    user: T.shape({
      autoId: T.number.isRequired,
      id: T.string.isRequired,
      username: T.string.isRequired,
      firstName: T.string.isRequired,
      lastName: T.string.isRequired
    }),
    dropUser: T.string,
    dropTeam: T.string,
    score: T.number,
    comment: T.string,
    valid: T.bool.isRequired,
    startDate: T.string.isRequired,
    lastEditionDate: T.string.isRequired,
    endDate: T.string,
    finished: T.bool.isRequired,
    editable: T.bool.isRequired,
    reported: T.bool.isRequired,
    reportedComment: T.string,
    correctionDenied: T.bool.isRequired,
    correctionDeniedComment: T.string,
    teamId: T.number,
    teamName: T.string,
    grades: T.arrayOf(T.shape(GradeType.propTypes))
  }
}

const DropType = {
  propTypes: {
    id: T.string.isRequired,
    user: T.shape({
      autoId: T.number.isRequired,
      id: T.string.isRequired,
      username: T.string.isRequired,
      firstName: T.string.isRequired,
      lastName: T.string.isRequired
    }),
    dropDate: T.string,
    score: T.number,
    finished: T.bool.isRequired,
    autoClosedDrop: T.bool.isRequired,
    unlockedDrop: T.bool.isRequired,
    unlockedUser: T.bool.isRequired,
    teamId: T.number,
    teamName: T.string,
    documents: T.arrayOf(
      T.shape(DocumentType.propTypes)
    ),
    corrections: T.arrayOf(
      T.shape(CorrectionType.propTypes)
    ),
    users: T.arrayOf(T.shape({
      autoId: T.number.isRequired,
      id: T.string.isRequired,
      username: T.string.isRequired,
      firstName: T.string.isRequired,
      lastName: T.string.isRequired
    })),
    comments: T.arrayOf(T.shape(Comment.propTypes))
  }
}

const Revision = {
  propTypes: {
    id: T.string.isRequired,
    creator: T.shape({
      autoId: T.number.isRequired,
      id: T.string.isRequired,
      username: T.string.isRequired,
      firstName: T.string.isRequired,
      lastName: T.string.isRequired
    }),
    creationDate: T.string,
    documents: T.arrayOf(T.shape(DocumentType.propTypes)),
    comments: T.arrayOf(T.shape(Comment.propTypes))
  }
}

export {
  DropzoneType,
  DropzoneToolDocumentType,
  DocumentType,
  GradeType,
  CorrectionType,
  DropType,
  Comment,
  Revision
}
