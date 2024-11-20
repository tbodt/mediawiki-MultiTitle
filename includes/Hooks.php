<?php

namespace MediaWiki\Extension\MultiTitle;

use MediaWiki\Hook\GetDoubleUnderscoreIDsHook;
use MediaWiki\Page\Hook\ArticleViewFooterHook;
use MediaWiki\Page\Hook\ArticleViewRedirectHook;
use PageProps;

class Hooks implements GetDoubleUnderscoreIDsHook, ArticleViewRedirectHook, ArticleViewFooterHook {
	private PageProps $mPageProps;

	public function __construct( PageProps $pageProps ) {
		$this->mPageProps = $pageProps;
	}

	public function onGetDoubleUnderscoreIDs( &$ids ) {
		$ids[] = 'keeptitle';
	}

	private function getPageProperty( $title, $propertyName ) {
		if ( !$title ) {
			return null;
		}
		$properties = $this->mPageProps->getProperties( $title, $propertyName );
		return $properties[$title->getId()] ?? null;
	}

	public function onArticleViewRedirect( $article ) {
		if ( $this->getPageProperty( $article->getRedirectedFrom(), 'keeptitle' ) !== null ) {
			return false;
		}
	}

	public function onArticleViewFooter( $article, $patrolFooterShown ) {
		$redirectTitle = $article->getRedirectedFrom();
		if ( $this->getPageProperty( $redirectTitle, 'keeptitle' ) === null ) {
			return;
		}

		$outputPage = $article->getContext()->getOutput();
		$redirectDisplayTitle = $this->getPageProperty( $redirectTitle, 'displaytitle' ) ?? $redirectTitle;
		$outputPage->setPageTitle( $redirectDisplayTitle );
		$outputPage->setDisplayTitle( $redirectDisplayTitle );
	}
}
