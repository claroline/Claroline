import {makeActionCreator} from '#/main/app/store/actions'
import {API_REQUEST} from '#/main/app/api'

export const MY_DEADLINE_LOAD = 'MY_DEADLINE_LOAD'

export const actions = {
  loadMyDeadline: makeActionCreator(MY_DEADLINE_LOAD, 'deadline'),

  /**
   * Fetches the computed deadline for the current user.
   */
  fetchMyDeadline(projectId) {
    return {
      [API_REQUEST]: {
        url: ['claro_project_my_deadline', {id: projectId}],
        success: (data, dispatch) => dispatch(actions.loadMyDeadline(data.deadline))
      }
    }
  },

  /**
   * Creates a submission for the current user.
   */
  createSubmission(projectId, data, milestoneId = null) {
    const url = milestoneId
      ? ['claro_project_milestone_submission_create', {id: projectId, milestoneId: milestoneId}]
      : ['claro_project_submission_create', {id: projectId}]

    return {
      [API_REQUEST]: {
        url: url,
        request: {
          method: 'POST',
          body: JSON.stringify(data)
        }
      }
    }
  },

  /**
   * Creates an annotation on a submission for a milestone (trainer).
   */
  createAnnotation(submissionId, milestoneId, content) {
    return {
      [API_REQUEST]: {
        url: ['claro_project_annotation_create', {id: submissionId, milestoneId: milestoneId}],
        request: {
          method: 'POST',
          body: JSON.stringify({content: content})
        }
      }
    }
  }
}
