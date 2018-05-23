import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {Row, Col} from 'react-bootstrap'
import {isArray} from 'lodash'
import {trans} from '#/main/core/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'

const DetailsField = (props) =>
  <Row>
    <Col xs={12} sm={4} md={2}><strong>{props.label}</strong></Col>
    <Col xs={12} sm={8} md={10}>{isArray(props.value) ? props.value.join(', ') : props.value}</Col>
  </Row>

DetailsField.props = {
  label: T.string.isRequired,
  value: T.oneOfType([T.string, T.number, T.array]).isRequired
}

const DetailsGroupField = (props) =>
  <div className="form-group">
    <label className="control-label" htmlFor={props.name}>{props.label}</label>
    <div id={props.name}>
      {props.children}
    </div>
  </div>

DetailsGroupField.propTypes = {
  name: T.string.isRequired,
  label: T.string.isRequired,
  children: T.node.isRequired
}

const UserDetails = (props) =>
  <div className={'user-details'}>
    <DetailsField label={trans('last_name', {}, 'platform')} value={props.user.lastName}/>
    <DetailsField label={trans('first_name', {}, 'platform')} value={props.user.firstName}/>
    {props.sessionId &&
    <DetailsField label={trans('session_id', {}, 'platform')} value={props.sessionId}/>
    }
    {props.user.platformRoles &&
    <DetailsField label={trans('log_platform_roles', {}, 'log')} value={props.user.platformRoles}/>
    }
    {props.user.workspaceRoles &&
    <DetailsField label={trans('log_workspace_roles', {}, 'log')} value={props.user.workspaceRoles}/>
    }
  </div>

UserDetails.propTypes = {
  user: T.shape({
    firstName: T.string.isRequired,
    lastName: T.string.isRequired,
    platformRoles: T.array,
    workspaceRoles: T.array
  }).isRequired,
  sessionId: T.string
}

const ResourceDetails = (props) =>
  <div className={'resource-details'}>
    <DetailsField label={trans('name', {}, 'platform')} value={props.resource.name}/>
    <DetailsField label={trans('path', {}, 'platform')} value={props.resource.path}/>
    {props.resourceType &&
    <DetailsField label={trans('type', {}, 'platform')} value={trans(props.resourceType, {}, 'resource')}/>
    }
  </div>

ResourceDetails.propTypes = {
  resource: T.object.isRequired,
  resourceType: T.string
}

const DoerDetails = (props) =>
  <DetailsGroupField name={'doer-details'} label={trans('log_doer', {}, 'log')}>
    {props.doerType === 'user' &&
    <UserDetails user={props.user}/>
    }
    {props.doerType === 'anonymous' &&
    <DetailsField label={trans('user', {}, 'platform')} value={trans('anonymous', {}, 'platform')}/>
    }
    {props.doerType === 'platform' &&
    <DetailsField label={trans('user', {}, 'platform')} value={trans('log_default_user', {}, 'log')}/>
    }
  </DetailsGroupField>

DoerDetails.propTypes = {
  user: T.object.isRequired,
  doerType: T.string.isRequired
}

const LogDetails = (props) =>
  <div className={'log-details'}>
    <div className={'panel panel-default'}>
      <div className={'panel-heading'}>
        <h3 className={'panel-title'} dangerouslySetInnerHTML={{__html: props.log.description}}/>
      </div>
      <div className="panel-body">
        {props.log.details &&
        <div className={'log-details-fields'}>
          {props.log.details.doer &&
          <DoerDetails user={props.log.details.doer} doerType={props.log.doerType}/>
          }
          {props.log.details.role &&
          <DetailsGroupField name={'role-details'} label={trans('log_role', {}, 'log')}>
            <DetailsField label={trans('name', {}, 'platform')} value={trans(props.log.details.role.name, {}, 'platform')}/>
          </DetailsGroupField>
          }
          {props.log.details.receiverUser &&
          <DetailsGroupField name={'receiver-details'} label={trans('log_receiver_user', {}, 'log')}>
            <UserDetails user={props.log.details.receiverUser}/>
          </DetailsGroupField>
          }
          {props.log.details.receiverGroup &&
          <DetailsGroupField name={'receiver-group-details'} label={trans('log_receiver_group', {}, 'log')}>
            <DetailsField label={trans('name', {}, 'platform')} value={props.log.details.receiverGroup.name}/>
          </DetailsGroupField>
          }
          {props.log.details.resource &&
          <DetailsGroupField name={'resource-details'} label={trans('log_resource', {}, 'log')}>
            <ResourceDetails resource={props.log.details.resource} resourceType={props.log.resourceType}/>
          </DetailsGroupField>
          }
          {props.log.details.owner &&
          <DetailsGroupField name={'owner-details'} label={trans('log_owner', {}, 'log')}>
            <UserDetails user={props.log.details.owner}/>
          </DetailsGroupField>
          }
          {props.log.details.workspace &&
          <DetailsGroupField name={'workspace-details'} label={trans('workspace', {}, 'platform')}>
            <DetailsField label={trans('name', {}, 'platform')} value={props.log.details.workspace.name}/>
          </DetailsGroupField>
          }
          {props.log.detailedDescription &&
          <DetailsGroupField name={'context-details'} label={trans('log_context', {}, 'log')}>
            <HtmlText>{props.log.detailedDescription}</HtmlText>
          </DetailsGroupField>
          }
        </div>
        }
      </div>
    </div>
  </div>

LogDetails.propTypes = {
  log: T.object.isRequired
}

const LogDetailsContainer = connect(
  state => ({
    log: state.log
  })
)(LogDetails)

export {
  LogDetailsContainer as LogDetails
}