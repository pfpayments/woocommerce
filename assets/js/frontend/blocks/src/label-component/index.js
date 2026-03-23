import React from 'react';

/**
 * Parses an HTML <img> tag string and extracts the src, alt, and width attributes.
 *
 * @param {string} imgTag The <img> tag string to parse.
 * @returns {Object} An object containing the src, alt, and width values.
 */
function parseImgTag(imgTag) {
    const srcMatch = imgTag.match(/src="([^"]+)"/);
	// Alt can be optional, hence *
    const altMatch = imgTag.match(/alt="([^"]*)"/);
    const widthMatch = imgTag.match(/width="([^"]+)"/);

    return {
        src: srcMatch ? srcMatch[1] : '',
        alt: altMatch ? altMatch[1] : '',
        width: widthMatch ? widthMatch[1] : '',
    };
}

/**
 * A React component that displays a label followed by an image.
 *
 * @param {string} label The text label to display.
 * @param {Object} props The props object containing src, alt, and width for the image.
 * @returns {JSX.Element} A JSX element representing the label and image.
 */
function LabelComponent({ label, src, alt, width }) {
    return (
        <div> {label} <img src={src} alt={alt} width={width} /> </div>
    );
}

// Exporting both functions
export { parseImgTag, LabelComponent };
