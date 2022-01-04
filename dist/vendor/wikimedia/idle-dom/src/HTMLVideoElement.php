<?php

// AUTOMATICALLY GENERATED.  DO NOT EDIT.
// Use `composer build` to regenerate.

namespace Wikimedia\IDLeDOM;

/**
 * HTMLVideoElement
 *
 * @see https://dom.spec.whatwg.org/#interface-htmlvideoelement
 *
 * @property int $nodeType
 * @property string $nodeName
 * @property string $baseURI
 * @property bool $isConnected
 * @property Document|null $ownerDocument
 * @property Node|null $parentNode
 * @property Element|null $parentElement
 * @property NodeList $childNodes
 * @property Node|null $firstChild
 * @property Node|null $lastChild
 * @property Node|null $previousSibling
 * @property Node|null $nextSibling
 * @property ?string $nodeValue
 * @property ?string $textContent
 * @property string $innerHTML
 * @property Element|null $previousElementSibling
 * @property Element|null $nextElementSibling
 * @property HTMLCollection $children
 * @property Element|null $firstElementChild
 * @property Element|null $lastElementChild
 * @property int $childElementCount
 * @property HTMLSlotElement|null $assignedSlot
 * @property ?string $namespaceURI
 * @property ?string $prefix
 * @property string $localName
 * @property string $tagName
 * @property string $id
 * @property string $className
 * @property DOMTokenList $classList
 * @property string $slot
 * @property NamedNodeMap $attributes
 * @property ShadowRoot|null $shadowRoot
 * @property string $outerHTML
 * @property CSSStyleDeclaration $style
 * @property string $contentEditable
 * @property string $enterKeyHint
 * @property bool $isContentEditable
 * @property string $inputMode
 * @property EventHandlerNonNull|callable|null $onload
 * @property DOMStringMap $dataset
 * @property string $nonce
 * @property int $tabIndex
 * @property string $title
 * @property string $lang
 * @property bool $translate
 * @property string $dir
 * @property bool $hidden
 * @property string $accessKey
 * @property string $accessKeyLabel
 * @property bool $draggable
 * @property bool $spellcheck
 * @property string $autocapitalize
 * @property string $innerText
 * @property Element|null $offsetParent
 * @property int $offsetTop
 * @property int $offsetLeft
 * @property int $offsetWidth
 * @property int $offsetHeight
 * @property ?string $crossOrigin
 * @property string $src
 * @property string $currentSrc
 * @property int $networkState
 * @property string $preload
 * @property TimeRanges $buffered
 * @property int $readyState
 * @property bool $seeking
 * @property float $currentTime
 * @property float $duration
 * @property bool $paused
 * @property float $defaultPlaybackRate
 * @property float $playbackRate
 * @property TimeRanges $played
 * @property TimeRanges $seekable
 * @property bool $ended
 * @property bool $autoplay
 * @property bool $loop
 * @property bool $controls
 * @property float $volume
 * @property bool $muted
 * @property bool $defaultMuted
 * @property AudioTrackList $audioTracks
 * @property VideoTrackList $videoTracks
 * @property TextTrackList $textTracks
 * @property int $width
 * @property int $height
 * @property int $videoWidth
 * @property int $videoHeight
 * @property string $poster
 * @property bool $playsInline
 * @phan-forbid-undeclared-magic-properties
 */
interface HTMLVideoElement extends HTMLMediaElement {
	// Direct parent: HTMLMediaElement

	/**
	 * @return int
	 */
	public function getWidth(): int;

	/**
	 * @param int $val
	 */
	public function setWidth( int $val ): void;

	/**
	 * @return int
	 */
	public function getHeight(): int;

	/**
	 * @param int $val
	 */
	public function setHeight( int $val ): void;

	/**
	 * @return int
	 */
	public function getVideoWidth(): int;

	/**
	 * @return int
	 */
	public function getVideoHeight(): int;

	/**
	 * @return string
	 */
	public function getPoster(): string;

	/**
	 * @param string $val
	 */
	public function setPoster( string $val ): void;

	/**
	 * @return bool
	 */
	public function getPlaysInline(): bool;

	/**
	 * @param bool $val
	 */
	public function setPlaysInline( bool $val ): void;

}
