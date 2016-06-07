function UnsafeFilter($sce) {
    return $sce.trustAsHtml;
}

export UnsafeFilter
