<td class="logo">
                        @php
                            if($isPdf){
                                //Default logo path
                                $defaultLogo = 'app/public/images/noimages/no-image-found.jpg';

                                //Company logo path
                                $companyLogoPath = 'app/public/images/company/';

                                $companyLogo = storage_path(
                                    !empty(app('company')['colored_logo']) && 
                                    file_exists(storage_path($companyLogoPath . app('company')['colored_logo']))
                                        ? $companyLogoPath . app('company')['colored_logo']
                                        : $defaultLogo
                                );
                            }else{
                                //Routing or direct view
                                $companyLogo = url('/company/getimage/'.app('company')['colored_logo']);
                            }
                        @endphp
                        <img src="{{ $companyLogo }}" alt="Logo" class="company-logo">
                    </td>
                    <td class="company-info">
                        <span class="company-name" >{{ app('company')['name'] }}</span>
                        <span><b>(1447932-K)</b></span>
                        <p class="company-contact">{{ app('company')['address'] }}<br>
                        <p>
                            @if(app('company')['mobile'])
                                <span>{{ app('company')['mobile'] ? 'Contact: '. app('company')['mobile'] : ''}}</span>
                            @endif
                        <br>
                            @if(app('company')['mobile'] || app('company')['email'])
                                <span>{{ app('company')['email'] ? ' Mail: '.app('company')['email'] : '' }}</span>
                            @endif
                        </p>
                        @if(app('company')['tax_number']!= '' && app('company')['tax_type'] != 'no-tax')
                            {{ app('company')['tax_type'] == 'gst' ? 'GST:' : __('tax.tax') . ':' }} {{ app('company')['tax_number'] }}<br>
                        @endif
                        </p>
                    </td>
