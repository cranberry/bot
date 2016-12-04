<?php

/*
 * This file is part of Cranberry\Bot
 */
namespace Cranberry\Bot;

use Cranberry\Core\String;
use Cranberry\Core\Utils;

class Knockov
{
	/**
	 * @var	array
	 */
	protected $sanitizers=[];

	/**
	 * @var	array
	 */
	protected $strings=[];

	/**
	 * @param	string	$startWord
	 * @param	float	$stopWordProbability
	 * @return	string
	 */
	public function getChain( $startWord=null, $stopWordProbability=0.3 )
	{
		$chainAttempts = 0;
		do
		{
			$currentWord = $startWord;
			$chainWords = [];

			if( !empty( $currentWord ) )
			{
				$chainWords[] = $currentWord;
			}

			do
			{
				$nextWordMetadata = $this->getNextWord( $currentWord );

				if( $nextWordMetadata['word'] !== false )
				{
					$normalizedNextWord = $nextWordMetadata['word'];
					$normalizedNextWord = $this->getNormalizedString( $normalizedNextWord );
					$chainWords[] = $nextWordMetadata['word'];

					// echo "{$normalizedNextWord} >> {$nextWordMetadata['sourceString']}" . PHP_EOL;
				}

				$shouldGetNextWord = true;
				$shouldGetNextWord = ($nextWordMetadata['word'] !== false) && $shouldGetNextWord;
				$shouldGetNextWord = $nextWordMetadata['stopWordProbability'] < $stopWordProbability && $shouldGetNextWord;

				$currentWord = $nextWordMetadata['word'];
			}
			while( $shouldGetNextWord );

			$chain = implode( ' ', $chainWords );

			/* Verify that the chain is not just a substring of a string */
			$shouldUseChain = true;
			$normalizedChain = $this->getNormalizedString( $chain );

			foreach( $this->strings as $variants )
			{
				$shouldUseChain = (substr_count( $variants['normalized'], $normalizedChain ) == 0) && $shouldUseChain;
			}

			$chainAttempts++;
			if( $chainAttempts >= 100 )
			{
				return false;
			}
		}
		while( !$shouldUseChain );

		return $chain;
	}

	/**
	 * Parse each normalized string looking for a next-word match
	 *
	 * @param	string	$currentWord
	 * @return	array|false
	 */
	public function getNextWord( $currentWord )
	{
		$matches = [];

		$currentWord = $this->getSanitizedString( $currentWord );
		$currentWord = $this->getNormalizedString( $currentWord );

		/* Starting a new chain */
		if( empty( $currentWord ) )
		{
			foreach( $this->strings as $variants )
			{
				$originalWords = explode( ' ', $variants['sanitized'] );

				if( isset( $originalWords[0] ) )
				{
					$matches[] = [ 'word' => $originalWords[0], 'sourceString' => $variants['sanitized'] ];
				}
			}
		}
		/* Find the next chain item */
		else
		{
			foreach( $this->strings as $variants )
			{
				$originalWords = explode( ' ', $variants['sanitized'] );
				$normalizedWords = explode( ' ', $variants['normalized'] );

				for( $w = 0; $w < count( $normalizedWords ); $w++ )
				{
					if( $normalizedWords[$w] == $currentWord )
					{
						if( isset( $normalizedWords[$w + 1] ) )
						{
							$matches[] = [ 'word' => $originalWords[$w + 1], 'sourceString' => $variants['sanitized'] ];
						}
					}
				}
			}
		}

		if( count( $matches ) == 0 )
		{
			$metadata['word'] = false;
		}
		else
		{
			$metadata = Utils::getRandomElement( $matches );
		}

		/*
		 * Metadata
		 */
		/* Number of occurrences and stop word probability */
		$numberOfOccurrences = 0;
		$stopWordProbability = 0;

		$normalizedNextWord = $metadata['word'];
		$normalizedNextWord = $this->getSanitizedString( $normalizedNextWord );
		$normalizedNextWord = $this->getNormalizedString( $normalizedNextWord );

		$occurrencesPattern = "/(^|\s){$normalizedNextWord}(\s|$)/i";
		$stopwordsPattern = "/(^|\s){$normalizedNextWord}(\?|\.|\!|$)(\s|$)/i";

		if( $metadata['word'] !== false )
		{
			$isStopWord = false;
			$numberOfStopWords = 0;

			foreach( $this->strings as $variants )
			{
				preg_match_all( $occurrencesPattern, $variants['normalized'], $occurrencesMatches );
				$numberOfOccurrences = $numberOfOccurrences + count( $occurrencesMatches[0] );

				preg_match_all( $stopwordsPattern, $variants['sanitized'], $stopwordsMatches );
				$numberOfStopWords = $numberOfStopWords + count( $stopwordsMatches[0] );
			}

			if( $numberOfOccurrences > 0 )
			{
				$stopWordProbability = $numberOfStopWords / $numberOfOccurrences;
			}
		}

		$metadata['numberOfOccurrences'] = $numberOfOccurrences;
		$metadata['stopWordProbability'] = $stopWordProbability;

		return $metadata;
	}

	/**
	 * @param	string	$string
	 * @return	string
	 */
	public function getNormalizedString( $string )
	{
		$normalizedString = $string;
		$normalizedString = String::strtolower( $normalizedString );

		/* Regular expression characters */
		$normalizedString = str_replace( '/', '\/', $normalizedString );
		$normalizedString = str_replace( '+', '\+', $normalizedString );
		$normalizedString = str_replace( '$', '\$', $normalizedString );
		$normalizedString = str_replace( '*', '\*', $normalizedString );

		/* Characters that belong to matched pairs */
		$normalizedString = str_replace( '"', '', $normalizedString );

		$normalizedString = str_replace( '.', '', $normalizedString );
		$normalizedString = str_replace( ',', '', $normalizedString );
		$normalizedString = str_replace( '?', '', $normalizedString );
		$normalizedString = str_replace( ':', '', $normalizedString );

		$normalizedString = str_replace( '&apos;', '’', $normalizedString );
		$normalizedString = str_replace( '’', '\'', $normalizedString );

		return $normalizedString;
	}

	/**
	 * @param	string	$string
	 * @return	string
	 */
	public function getSanitizedString( $string )
	{
		$sanitizedString = $string;

		foreach( $this->sanitizers as $sanitizer )
		{
			$sanitizedString = call_user_func( $sanitizer, $sanitizedString );
		}

		/* Whitespace */
		$sanitizedString = trim( $sanitizedString );
		$sanitizedString = str_replace( "\n", ' ', $sanitizedString );
		$sanitizedString = str_replace( "\t", ' ', $sanitizedString );

		while( substr_count( $sanitizedString, '  ' ) > 0 )
		{
			$sanitizedString = str_replace( '  ', ' ', $sanitizedString );
		}

		/* Characters that belong to matched pairs */
		$sanitizedString = str_replace( '[', '', $sanitizedString );
		$sanitizedString = str_replace( ']', '', $sanitizedString );
		$sanitizedString = str_replace( '(', '', $sanitizedString );
		$sanitizedString = str_replace( ')', '', $sanitizedString );
		$sanitizedString = str_replace( ' ‘', ' ', $sanitizedString );

		return $sanitizedString;
	}

	/**
	 * @param	Closure	$sanitizer
	 */
	public function registerSanitizer( \Closure $sanitizer )
	{
		$this->sanitizers[] = $sanitizer;
	}

	/**
	 * @param	string	$string
	 */
	public function registerString( $string )
	{
		$sanitizedString = $this->getSanitizedString( $string );

		$variants['original'] = $string;
		$variants['sanitized'] = $sanitizedString;
		$variants['normalized'] = self::getNormalizedString( $sanitizedString );

		$this->strings[] = $variants;
	}
}
