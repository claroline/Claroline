import React from 'react'
import {useSelector} from 'react-redux'
import Accordion from 'react-bootstrap/Accordion'
import {trans, transChoice} from '#/main/app/intl/translation'
import {PageContent, PageSection} from '#/main/app/page'
import {Badge} from '#/main/app/components/badge'
import get from 'lodash/get'
import {ActivityChart} from '#/main/evaluation/chart/components/activity'

const HomeDashboardOverview = () => {
  const tabs = useSelector(state => get(state, 'home.tabs', []))

  return (
    <PageContent className="d-flex flex-column py-4 gap-5">
      <PageSection
        size="xl"
        title={trans('views')}
      >
        {tabs.length === 0 ? (
          <div />
        ) : (
          <Accordion
            defaultActiveKey={tabs[0] ? String(tabs[0].id) : undefined}
          >
            {tabs.map((tab) => {
              const views = get(tab, 'meta.views', 0)
              return (
                <Accordion.Item
                  key={tab.id}
                  eventKey={String(tab.id)}
                >
                  <Accordion.Header>
                    <div className="d-flex justify-content-between align-items-center w-100 pe-3">
                      <div className="text-truncate">
                        {tab.title || ''}
                      </div>

                      <Badge variant="secondary" subtle>
                        <span className="fa fa-eye me-2" />
                        {transChoice('display_views', views, {count: views})}
                      </Badge>
                    </div>
                  </Accordion.Header>
                  <Accordion.Body>
                    <ActivityChart
                      name={`homeActivity${tab.id}`}
                      activityUrl={(activityType) => [
                        'apiv2_home_tab_activity',
                        {id: tab.id, activityType: activityType}
                      ]}
                      viewUrl={['apiv2_home_tab_views', {id: tab.id}]}
                    />
                  </Accordion.Body>
                </Accordion.Item>
              )
            })}
          </Accordion>
        )}
      </PageSection>
    </PageContent>
  )
}

export {
  HomeDashboardOverview
}
