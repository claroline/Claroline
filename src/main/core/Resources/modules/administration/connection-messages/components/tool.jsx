import React from 'react';
import {Routes} from '#/main/app/router';
import {MessagesList} from './messagesList.jsx';
import {LINK_BUTTON} from "#/main/app/buttons";
import {trans} from "#/main/app/intl";
import {ToolPage} from "#/main/core/tool/containers/page";
import {Message} from "#/main/core/administration/connection-messages/containers/message";
import {PropTypes as T} from "prop-types";
const ConnectionMessagesTool = (props) => {
    const messagesActions = [];

    switch (props.path) {
        case props.path + '/messages':
            messagesActions.push({
                name: 'add',
                type: LINK_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('add_connection_message'),
                target: props.path + '/messages/form',
                primary: true
            });
            break;
    }

    return (
        <ToolPage
            title={trans('connection_messages', {}, 'tools')}
            actions={messagesActions}
        >

            <Routes
                routes={[
                    {
                        path: "/",
                        component: MessagesList
                    },
                    {
                        path: '/messages/form/:id?',
                        component: Message,
                        // onEnter: (params) =>props.openConnectionMessageForm(params.id),
                        // onLeave: () =>props.resetConnectionMessageFrom()
                    },
                ]}
            />
        </ToolPage>
    );
};

ConnectionMessagesTool.propTypes = {
    path: T.string,
    location: T.shape({
        pathname: T.string
    }),
    openConnectionMessageForm: T.func,
    resetConnectionMessageFrom: T.func
};

export { ConnectionMessagesTool };
