import React from 'react';
import { trans } from '#/main/app/intl/translation';
import { ToolPage } from '#/main/core/tool/containers/page';
import { LINK_BUTTON, MODAL_BUTTON } from '#/main/app/buttons';
import { MODAL_CONNECTION } from '#/main/app/modals/connection';
import { ListData } from '#/main/app/content/list/containers/data';
import { constants } from '#/main/core/data/types/connection-message/constants';
import { selectors } from '#/main/core/administration/connection-messages/store/selectors';
import { ConnectionMessageCard } from '#/main/core/data/types/connection-message/components/card';

const MessagesList = (props) => {
    return (
        <ListData
            name={selectors.STORE_NAME + '.messages.list'}
            fetch={{
                url: ['apiv2_connectionmessage_list'],
                autoload: true
            }}
            primaryAction={(row) => ({
                type: LINK_BUTTON,
                target: `${props.path}/messages/form/${row.id}`,
                label: trans('edit', {}, 'actions')
            })}
            actions={(rows) => [{
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-pencil',
                label: trans('edit', {}, 'actions'),
                scope: ['object'],
                target: `${props.path}/messages/form/${rows[0].id}`,
                displayed: !rows[0].locked
            }, {
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-eye',
                label: trans('preview', {}, 'actions'),
                scope: ['object', 'collection'],
                modal: [MODAL_CONNECTION, {
                    messages: rows,
                    noDiscard: true
                }]
            }]}
            delete={{
                url: ['apiv2_connectionmessage_delete_bulk'],
                displayed: (rows) => !rows.find(message => message.locked)
            }}
            definition={[{
                name: 'title',
                type: 'string',
                label: trans('title'),
                displayed: true,
                primary: true
            }, {
                name: 'type',
                type: 'choice',
                label: trans('type'),
                options: {
                    choices: constants.MESSAGE_TYPES
                },
                displayed: true
            }, {
                name: 'restrictions.dates[0]',
                alias: 'startDate',
                type: 'date',
                label: trans('start_date'),
                displayed: true
            }, {
                name: 'restrictions.dates[1]',
                alias: 'endDate',
                type: 'date',
                label: trans('end_date'),
                displayed: true
            }, {
                name: 'restrictions.roles',
                type: 'roles',
                label: trans('roles'),
                displayed: true,
                filterable: false
            }, {
                name: 'restrictions.hidden',
                type: 'boolean',
                label: trans('hidden')
            }]}
            card={ConnectionMessageCard}
        />
    );
};

export { MessagesList };

