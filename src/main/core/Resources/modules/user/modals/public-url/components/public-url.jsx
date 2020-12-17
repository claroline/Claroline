import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {User as UserType} from '#/main/core/user/prop-types'
import {ContentHtml} from '#/main/app/content/components/html'
import {FormDataModal} from '#/main/app/modals/form/components/data'

class PublicUrlModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      url: this.props.url
    }
  }

  render() {
    return (
      <FormDataModal
        {...omit(this.props, 'user', 'url', 'redirect', 'changeUrl', 'onSave')}
        icon="fa fa-fw fa-link"
        title={trans('change_profile_public_url')}
        data={{
          url: this.state.url
        }}
        save={(data) => this.props.changeUrl(this.props.user, data.url, this.props.redirect, this.props.onSave)}
        sections={[
          {
            id: 'general',
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'preview',
                type: 'string',
                label: trans('profile_public_url_preview'),
                hideLabel: true,
                render: () =>
                  <ContentHtml className="read-only">
                    {'#/desktop/community/profile/'+ this.state.url}
                  </ContentHtml>
              }, {
                name: 'url',
                type: 'string',
                label: trans('profile_public_url'),
                help: [
                  trans('public_url_help'),
                  trans('public_url_format_help')
                ],
                onChange: (value) => this.setState({url: value}), // this is just to have a nice preview
                required: true,
                options: {
                  maxLength: 30,
                  regex: /[a-zA-Z0-9\-_.]/g
                }
              }
            ]
          }
        ]}
      />
    )
  }
}

PublicUrlModal.propTypes = {
  user: T.shape(UserType.propTypes),
  url: T.string,
  redirect: T.bool,
  changeUrl: T.func.isRequired,
  onSave: T.func
}

PublicUrlModal.defaultProps = {
  redirect: false,
  onSave: () => false
}

export {
  PublicUrlModal
}
