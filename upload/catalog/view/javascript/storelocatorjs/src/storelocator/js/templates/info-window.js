import svgRoute from '../../svg/route.svg'

/**
 * storelocatorjs info window template
 * @module storelocatorjs/templateInfoIndow
 */
export default function ({store, origin}) {
	return `
		<div class="storelocator-infoWindow">
			${store.picture
				? `<span class="storelocator-pictureStore">
					<img src="${store.picture}" alt="${store.title}" />
				</span>`
			: ``}
			<div class="storelocator-detailStore">
				${store.title
					? `<span class="storelocator-detailStoreTitle">${store.index + 1}. ${store.title}</span>`
				: ``}
				<a href="http://www.google.fr/maps/dir/${origin}/${store.lat},${store.lng}" title="Abrir no Google Maps" target="_blank" class="storelocator-detailStoreDistance">
					<span>${store.distance}km</span>
					${svgRoute}
					</a>
				${store.address
					? `<span class="storelocator-detailStoreAddress">${store.address}</span>`
				: ``}
				${store.zipcode
					? `<span class="storelocator-detailStoreZipcode">${store.zipcode}</span>`
				: ``}
				${store.city
					? `<span class="storelocator-detailStoreCity">${store.city}</span>`
				: ``}
				${store.phone
					? `<span class="storelocator-detailStorePhone"><a href="tel:${store.phone}" title="Ligar">${store.phone}</a></span>`
				: ``}
				${store.email
					? `<span class="storelocator-detailStoreEmail"><a href="tel:${store.email}" title="Email">${store.email}</a></span>`
				: ``}
				${typeof store.link !== 'undefined'
				? `<span class="storelocator-detailStoreSite"><i class="fa fa-link"></i> <a href="${store.link}" title="Site" target="_blank" class="storelocator-detailStoreUrl">${store.link}</a></span>`
				: ``}
			</div>
		</div>`
}
