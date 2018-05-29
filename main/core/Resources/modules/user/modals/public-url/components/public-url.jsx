import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {DataFormModal} from '#/main/core/data/form/modals/components/data-form'

class PublicUrlModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      url: this.props.url
    }
  }

  render() {
    return (
      <DataFormModal
        {...omit(this.props, 'url', 'changeUrl')}
        icon="fa fa-fw fa-link"
        title={trans('change_profile_public_url')}
        data={{
          url: this.state.url
        }}
        save={(data) => this.props.changeUrl(data.url)}
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
                calculated: () =>
                  <HtmlText className="read-only">
                    {url(['claro_user_profile', {publicUrl: this.state.url}, true]).replace(this.state.url, `<b>${this.state.url}</b>`)}
                  </HtmlText>,
                hideLabel: true,
                readOnly: true
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
  url: T.string,
  changeUrl: T.func.isRequired
}

export {
  PublicUrlModal
}
