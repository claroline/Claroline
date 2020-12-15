import {connect} from 'react-redux'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {selectors, reducer} from '#/plugin/cursus/event/modals/parameters/store'
import {EventFormModal as EventFormModalComponent} from '#/plugin/cursus/event/modals/parameters/components/modal'
import {Event as EventTypes} from '#/plugin/cursus/prop-types'

const EventFormModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      loadEvent(event = null, session = null) {
        let formData
        if (event) {
          formData = event
        } else {
          formData = merge(
            {
              meta: {
                session: {
                  id: session.id,
                  name: session.name,
                  code: session.code,
                  slug: session.slug
                }
              }
            }, EventTypes.defaultProps, omit(session, 'id')
          ) // todo : omit props not needed for events
        }

        dispatch(formActions.resetForm(selectors.STORE_NAME, formData, !event))
      },
      saveEvent(eventId = null, onSave = () => true) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, eventId ? ['apiv2_cursus_event_update', {id: eventId}] : ['apiv2_cursus_event_create'])).then((response) => {
          if (onSave) {
            onSave(response)
          }
        })
      }
    })
  )(EventFormModalComponent)
)

export {
  EventFormModal
}
