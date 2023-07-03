import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/announcement/resources/announcement/store'

const AnnouncesEditor = props => {

    console.log('AnnouncesEditor props', props)

    return <FormData
        name={selectors.STORE_NAME + '.announcementForm'}
        title={trans('parameters')}
        buttons={true}
        save={{
            type: CALLBACK_BUTTON,
            callback: () => props.saveForm(props.announcement.id, props.announcementForm)
        }}
        cancel={{
            type: LINK_BUTTON,
            target: props.path,
            exact: true
        }}
        definition={[{
            title: trans('general'),
            primary: true,
            fields: [{
                name: 'templateEmail',
                label: trans('email_announcement', {}, 'template'),
                type: 'template',
                options: {
                    picker: {
                        filters: [{
                            property: 'typeName',
                            value: 'email_announcement',
                            locked: true
                        }]
                    }
                }
            }]
        }]}
    />;
}

export {
    AnnouncesEditor
}
