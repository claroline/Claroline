import {connect} from 'react-redux'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors, reducer} from '#/plugin/cursus/administration/modals/session-form/store'
import {SessionFormModal as SessionFormModalComponent} from '#/plugin/cursus/administration/modals/session-form/components/modal'
import {Session as SessionTypes} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionFormModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadSession(session = null, course = null) {
        let formData
        if (session) {
          formData = session
        } else {
          formData = merge(
            {
              meta: {
                course: {
                  id: course.id,
                  name: course.name,
                  code: course.code,
                  slug: course.slug
                }
              }
            }, SessionTypes.defaultProps, omit(course, 'id')
          ) // todo : omit props not needed for sessions
        }

        dispatch(formActions.resetForm(selectors.STORE_NAME, formData, !session))
      },
      saveSession(sessionId = null, onSave = () => true) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, sessionId ? ['apiv2_cursus_session_update', {id: sessionId}] : ['apiv2_cursus_session_create'])).then((response) => {
          if (onSave) {
            onSave(response)
          }
        })
      }
    })
  )(SessionFormModalComponent)
)

export {
  SessionFormModal
}
