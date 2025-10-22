import React, {useEffect, useState} from 'react'
import {useDispatch} from 'react-redux'

import {PlaceholderText} from '#/main/app/components/placeholder'
import {PrivacySummary} from '#/main/privacy/components/summary'
import {API_REQUEST} from '#/main/app/api'
import {Html} from '#/main/app/components/html'

const Privacy = () => {
  const dispatch = useDispatch()

  const [loaded, setLoaded] = useState(false)
  const [privacy, setPrivacy] = useState(null)

  useEffect(() => {
    dispatch({
      [API_REQUEST]: {
        url: ['apiv2_privacy_get'],
        silent: true,
        success: (response) => {
          setLoaded(true)
          setPrivacy(response)
        }
      }
    })
  }, [loaded])

  if (!loaded) {
    return (
      <PlaceholderText
        level={1}
        paragraphs={3}
      />
    )
  }

  if (privacy) {
    return (
      <>
        <PrivacySummary
          className="mb-4"
          dpo={privacy.dpo}
          countryStorage={privacy.countryStorage}
        />
        <Html className="content-text">{privacy.content}</Html>
      </>
    )
  }

  return null
}

export {
  Privacy
}
