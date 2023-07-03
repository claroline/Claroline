import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {AnnouncesEditor as AnnouncesEditorComponent} from '#/plugin/announcement/resources/announcement/components/editor'
import {actions as formActions}                      from "#/main/app/content/form/store";
import {selectors}                                   from "#/plugin/announcement/resources/announcement/store";

const AnnouncesEditor = connect(
    state => ({
        path: resourceSelectors.path(state),
        announcement: selectors.announcement(state)
    }),
    dispatch => ({
        resetForm(data, isNew) {
            dispatch(formActions.resetForm(selectors.STORE_NAME+'.announcementForm', data, isNew))
        },
        saveForm(aggregateId, data, history, onSuccess, path) {
            console.log('AnnouncesEditor saveForm', aggregateId, history, onSuccess, path)
            dispatch(formActions.saveForm(
                selectors.STORE_NAME+'.announcementForm',
                ['claro_announcement_aggregate_update', {aggregateId: aggregateId}]
            )).then(
                (announcement) => {
                    onSuccess(announcement)
                }
            )
        }
    })
)(AnnouncesEditorComponent)

export {
    AnnouncesEditor
}
