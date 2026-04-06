import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {Html} from '#/main/app/components/html'
import {ContentHtml} from '#/main/app/content/components/html'
import {PageSection} from '#/main/app/page/components/section'
import {ResourcePage} from '#/main/core/resource/components/page'

import {selectors} from '#/plugin/project/resources/project/store'

const ProjectPlayer = () => {
  const project = useSelector(selectors.project)

  if (!project) {
    return null
  }

  return (
    <ResourcePage>
      {project.instruction &&
        <PageSection
          title={trans('instruction', {}, 'resource')}
          size="lg"
          className="py-3"
        >
          <ContentHtml>{project.instruction}</ContentHtml>
        </PageSection>
      }

      <PageSection
        title={trans('submission', {}, 'resource')}
        size="lg"
        className="py-3"
      >
        {project.allowFileUpload &&
          <div className="mb-3">
            <label className="form-label">{trans('file', {}, 'resource')}</label>
            <div className="drop-zone-placeholder text-center p-5 border rounded bg-body-tertiary">
              <span className="fa fa-fw fa-cloud-upload fa-3x text-secondary mb-3 d-block" />
              <span className="text-secondary">{trans('drop_file_here', {}, 'resource')}</span>
            </div>
          </div>
        }

        {project.allowRichText &&
          <div className="mb-3">
            <label className="form-label">{trans('rich_text', {}, 'resource')}</label>
            <div className="border rounded p-3 bg-body-tertiary text-secondary">
              {trans('rich_text_placeholder', {}, 'resource')}
            </div>
          </div>
        }

        {project.allowUrl &&
          <div className="mb-3">
            <label className="form-label">{trans('url', {}, 'resource')}</label>
            <input
              type="url"
              className="form-control"
              placeholder={trans('url_placeholder', {}, 'resource')}
              disabled={true}
            />
          </div>
        }

        {project.allowMedia &&
          <div className="mb-3">
            <label className="form-label">{trans('media', {}, 'resource')}</label>
            <div className="drop-zone-placeholder text-center p-5 border rounded bg-body-tertiary">
              <span className="fa fa-fw fa-photo-video fa-3x text-secondary mb-3 d-block" />
              <span className="text-secondary">{trans('drop_media_here', {}, 'resource')}</span>
            </div>
          </div>
        }

        {!project.allowFileUpload && !project.allowRichText && !project.allowUrl && !project.allowMedia &&
          <div className="text-secondary text-center p-5">
            {trans('no_submission_required', {}, 'resource')}
          </div>
        }
      </PageSection>
    </ResourcePage>
  )
}

export {
  ProjectPlayer
}
