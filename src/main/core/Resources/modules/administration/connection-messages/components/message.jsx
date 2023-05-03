import {FormData}                                    from "#/main/app/content/form/containers/data";
import {trans}                                       from "#/main/app/intl";
import {selectors}                                   from "#/main/core/administration/parameters/store";
import {LINK_BUTTON}                                 from "#/main/app/buttons";
import {constants}                                   from "#/main/core/data/types/connection-message/constants";
import {PropTypes as T}                              from "prop-types";
import {ConnectionMessage as ConnectionMessageTypes} from "#/main/core/data/types/connection-message/prop-types";
import React                                         from "react";

const Message = (props) =>
    <FormData
        level={2}
        title={props.new ? trans('connection_message_creation') : trans('connection_message_edition')}
        name={selectors.STORE_NAME+'.messages.current'}
        target={(message, isNew) => isNew ?
            ['apiv2_connectionmessage_create'] :
            ['apiv2_connectionmessage_update', {id: message.id}]
        }
        buttons={true}
        disabled={props.message.locked}
        cancel={{
            type: LINK_BUTTON,
            target: props.path+'/messages',
            exact: true
        }}
        sections={[
            {
                title: trans('general'),
                primary: true,
                fields: [
                    {
                        name: 'title',
                        type: 'string',
                        label: trans('title'),
                        required: true
                    }, {
                        name: 'type',
                        type: 'choice',
                        label: trans('type'),
                        required: true,
                        options: {
                            condensed: true,
                            noEmpty: true,
                            choices: constants.MESSAGE_TYPES
                        }
                    }
                ]
            }, {
                icon: 'fa fa-fw fa-key',
                title: trans('access_restrictions'),
                fields: [
                    {
                        name: 'restrictions.hidden',
                        type: 'boolean',
                        label: trans('restrict_hidden')
                    }, {
                        name: 'restrictions.enableDates',
                        label: trans('restrict_by_dates'),
                        type: 'boolean',
                        calculated: restrictedByDates,
                        onChange: activated => {
                            if (!activated) {
                                props.updateProp('restrictions.dates', [])
                            }
                        },
                        linked: [
                            {
                                name: 'restrictions.dates',
                                type: 'date-range',
                                label: trans('access_dates'),
                                displayed: restrictedByDates,
                                required: true,
                                options: {
                                    time: true
                                }
                            }
                        ]
                    }, {
                        name: 'restrictions.enableRoles',
                        label: trans('restrict_by_roles'),
                        type: 'boolean',
                        calculated: restrictedByRoles,
                        onChange: activated => {
                            if (!activated) {
                                props.updateProp('restrictions.roles', [])
                            }
                        },
                        linked: [
                            {
                                name: 'restrictions.roles',
                                label: trans('roles'),
                                type: 'roles',
                                displayed: restrictedByRoles,
                                required: true
                            }
                        ]
                    }
                ]
            }
        ]}
    >
        <SlidesForm
            slides={props.message.slides || []}
            disabled={props.message.locked}
            createSlide={props.createSlide}
            updateProp={props.updateProp}
        />
    </FormData>

Message.propTypes = {
    path: T.string,
    new: T.bool,
    message: T.shape(
        ConnectionMessageTypes.propTypes
    ),
    createSlide: T.func.isRequired,
    updateProp: T.func.isRequired
}

export {
    Message
}
