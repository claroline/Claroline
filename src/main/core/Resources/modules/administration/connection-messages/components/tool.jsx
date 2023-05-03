import React from 'react';
import { Routes } from '#/main/app/router';
import { Messages } from '#/main/core/administration/connection-messages/components/messages';
import { LINK_BUTTON } from '#/main/app/buttons';
import { trans } from '#/main/app/intl';
import { ToolPage } from '#/main/core/tool/containers/page';
import { Message } from '#/main/core/administration/connection-messages/components/message';
import { PropTypes as T } from 'prop-types';

const ConnectionMessagesTool = (props) => {
    return (
        <ToolPage
            title={trans('connection_messages', {}, 'tools')}
        >
            <Routes
                path={props.path}
                routes={[
                    {
                        path: '/',
                        render: () => <Messages />
                    }
                ]}
            />
        </ToolPage>
    );
};

ConnectionMessagesTool.propTypes = {
    path: T.string,
    location: T.shape({
        pathname: T.string,
    }),
    openConnectionMessageForm: T.func,
    resetConnectionMessageForm: T.func,
};

export { ConnectionMessagesTool };
