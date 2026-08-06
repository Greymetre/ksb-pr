<style>
    .board {
        display: flex;
        gap: 20px;
        padding: 20px;
        background: #fff;
        flex-wrap: no-wrap;
        width: {{$opportunity_status->count()*30}}%;
    }

    .skolling{
        overflow-x: scroll;
    }

    .column {
        box-sizing: border-box;
        width: 100%;
        background: #fff;
        padding: 15px;
        /*min-height: 300px;*/
        height: 100%;
        box-shadow: unset!important;
        border-radius: 4px !important;
        border: 1px solid #E2E2E2!important;
        overflow: hidden;
    }

    .column h3 {
        text-align: center;
        margin-bottom: 10px;
    }

    .card {
        background: #e0e0e0;
        margin: 10px 0;
        padding: 10px;
        border-radius: 8px;
        cursor: grab;
        transition: background 0.2s;
    }

    .card:hover {
        background: #d5d5d5;
    }

    .drag-header h3 {
        margin: 0px;
        border: 1px solid #2C3D67;
        background: #2C3D67;
        font-size: 14px;
        font-weight: 600;
        line-height: 12px;
        text-transform: uppercase;
        letter-spacing: 0.25px;
        border-radius: 50px;
        padding: 3px 15px;
        display: inline-flex;
    }

    .apprtunity-price {
        color: #242424;
        font-size: 13px;
        font-weight: 400;
        line-height: 16px;
        letter-spacing: 0.5px;
        margin-top: 15px;
    }

    .flex.anulprice p {
        margin: 0px;
    }

    p.same {
    color: #5E5E5E;
    font-size: 13px;
    font-weight: 600;
    line-height: 14px;
    letter-spacing: 0.5px;
    margin-top: 8px;
    width: 135px;
}

p.dam {
    background: #E5F3FF;
    border-radius: 50px;
    color: #2E2E2E;
    font-size: 15px !important;
    font-weight: 500 !important;
    line-height: 14px;
    padding: 5px 5px;
    width: auto !important;
    height: 36px;
    text-align: center;
    display: flex;
    justify-content: center;
    align-items: center;
}

    .flex.anulprice {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .card_drag .card-header {
        border-bottom: 1px solid #E8E8E8 !important;
    }

    .card_drag .card-header p {
        color: #4B4B4B;
        font-size: 14px;
        font-weight: 600;
        line-height: 12px;
        margin-bottom: 0px;
        letter-spacing: 0px;
    }

    .img_dd img {
        margin-right: 10px;
        border-radius: 50px;
    }

    .card_drag .card-header .header_image img {
        width: 25px;
        height: 25px;
        object-fit: cover;
        margin-right: 9px;
    }

  /*  .card-body.bgrid.column_drag {
        padding: .9375rem 12px!important;
    }
*/
    .card_drag .card-body {
    padding: .9375rem 6px;
}


    .card_drag .data-ss p {
        margin: 0px;
        color: #5E5E5E;
        font-size: 12px;
        font-weight: 400;
        line-height: 12px;
        letter-spacing: 0px;
    }

    .column_drag
     {
        min-height: 500px;
    }

    .bgrid {
        background: #F2F2F4;
    }

    .card_drag .data-ss h5 {
        font-size: 14px;
        color: #2E2E2E;
        line-height: 14px;
        margin-bottom: 2px;
        font-weight: 600;
        letter-spacing: 0px;
    }

    button.hoverbtn {
        width: 20px;
        height: 20px;
        background: transparent!important;
        box-shadow: unset;
        padding: 0px;
        position: absolute;
        top: -5px;
        right: 1px;
        opacity: 0;
    }
    button.hoverbtn2 {
        width: 20px;
        height: 20px;
        background: transparent!important;
        box-shadow: unset;
        padding: 0px;
        position: absolute;
        top: -5px;
        right: 25px;
        opacity: 0;
    }

    button.hoverbtn img {
        width: 17px;
        height: auto;
    }

    .card_drag .card-header {
        position: relative;
    }

.card_drag .card-header:hover button.hoverbtn {opacity: 1;}
.card_drag .card-header:hover button.hoverbtn2 {opacity: 1;}

@media (max-width: 767px){

 .board{
    flex-direction:column;
 }

.column_drag {
    min-height: auto;
 }
}

/* Scoped Kanban styling for the current CRM shell. */
.opportunity-board-page .skolling {
    overflow-x: auto;
    overflow-y: hidden;
    padding: 18px;
    scrollbar-color: rgba(34, 211, 238, .42) rgba(8, 19, 42, .8);
    scrollbar-width: thin;
}

.opportunity-board-page .board {
    width: max-content !important;
    min-width: 100%;
    align-items: flex-start;
    gap: 14px;
    padding: 0;
    background: transparent;
}

.opportunity-board-page .column {
    flex: 0 0 304px;
    width: 304px;
    min-width: 304px;
    overflow: hidden;
    border: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22)) !important;
    border-radius: 13px !important;
    background: rgba(8, 19, 42, .68) !important;
    box-shadow: none !important;
}

.opportunity-board-page .column > .card {
    border: 0 !important;
    border-radius: 0 !important;
    background: transparent !important;
    box-shadow: none !important;
}

.opportunity-board-page .drag-header {
    min-height: 142px;
    padding: 16px !important;
    border: 0 !important;
    border-bottom: 1px solid var(--fk-list-border, rgba(90, 130, 220, .22)) !important;
    background: rgba(10, 25, 52, .94) !important;
}

.opportunity-board-page .drag-header h3 {
    min-height: 27px;
    margin: 0 0 14px;
    padding: 7px 11px;
    border: 1px solid rgba(34, 211, 238, .28);
    border-radius: 8px;
    background: rgba(34, 211, 238, .10);
    color: var(--fk-list-accent, #22d3ee);
    font-family: 'Sora', 'Inter', sans-serif;
    font-size: 10px;
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: 1.2px;
}

.opportunity-board-page .apprtunity-price {
    margin: 0 0 15px;
    color: var(--fk-list-dim, #8291ad);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.1px;
}

.opportunity-board-page p.same {
    width: auto;
    margin: 0;
    color: var(--fk-list-soft, #c8d5ea);
    font-size: 11px;
    font-weight: 600;
}

.opportunity-board-page p.dam {
    min-width: 82px;
    height: auto;
    margin: 0;
    padding: 7px 10px;
    border: 1px solid rgba(52, 211, 153, .24);
    border-radius: 8px;
    background: rgba(52, 211, 153, .09);
    color: #6ee7b7;
    font-size: 12px !important;
    font-weight: 800 !important;
}

.opportunity-board-page .column_drag {
    min-height: 390px;
    padding: 12px !important;
    background: rgba(3, 11, 28, .52) !important;
}

.opportunity-board-page .column_drag:empty::after {
    content: 'Drop opportunity here';
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 92px;
    border: 1px dashed rgba(90, 130, 220, .28);
    border-radius: 10px;
    color: var(--fk-list-dim, #8291ad);
    font-size: 11px;
}

.opportunity-board-page .card_drag {
    margin: 0 0 10px;
    overflow: hidden;
    border: 1px solid rgba(90, 130, 220, .20) !important;
    border-radius: 11px !important;
    background: rgba(12, 28, 57, .92) !important;
    box-shadow: 0 8px 22px rgba(0, 0, 0, .14) !important;
    transition: transform .18s ease, border-color .18s ease, background .18s ease;
}

.opportunity-board-page .card_drag:hover {
    transform: translateY(-2px);
    border-color: rgba(34, 211, 238, .42) !important;
    background: rgba(15, 35, 70, .98) !important;
}

.opportunity-board-page .card_drag .card-header {
    min-height: 58px;
    padding: 11px 70px 11px 11px !important;
    border-bottom: 1px solid rgba(90, 130, 220, .16) !important;
    background: transparent !important;
}

.opportunity-board-page .card_drag .card-header .text {
    min-width: 0;
}

.opportunity-board-page .card_drag .card-header h5,
.opportunity-board-page .card_drag .data-ss h5 {
    color: var(--fk-list-heading, #f1f5ff);
}

.opportunity-board-page .card_drag .card-header p,
.opportunity-board-page .card_drag .data-ss p {
    overflow: hidden;
    color: var(--fk-list-dim, #8291ad);
    text-overflow: ellipsis;
    white-space: nowrap;
}

.opportunity-board-page .card_drag .card-body {
    padding: 12px !important;
    background: transparent !important;
}

.opportunity-board-page .card_drag .header_image img,
.opportunity-board-page .card_drag .img_dd img {
    width: 28px;
    height: 28px;
    padding: 5px;
    border: 1px solid rgba(34, 211, 238, .20);
    border-radius: 8px;
    background: rgba(34, 211, 238, .08);
}

.opportunity-board-page button.hoverbtn,
.opportunity-board-page button.hoverbtn2 {
    top: 10px;
    width: 28px;
    height: 28px;
    padding: 4px !important;
    border: 1px solid rgba(90, 130, 220, .24) !important;
    border-radius: 7px !important;
    background: rgba(7, 17, 37, .85) !important;
    opacity: .72;
}

.opportunity-board-page button.hoverbtn { right: 10px; }
.opportunity-board-page button.hoverbtn2 { right: 43px; }
.opportunity-board-page button.hoverbtn:hover,
.opportunity-board-page button.hoverbtn2:hover { opacity: 1; border-color: rgba(34, 211, 238, .5) !important; }
.opportunity-board-page button.hoverbtn2 i { color: var(--fk-list-accent, #22d3ee) !important; font-size: 17px; }

@media (max-width: 767px) {
    .opportunity-board-page .skolling { padding: 12px; }
    .opportunity-board-page .board { width: 100% !important; }
    .opportunity-board-page .column { flex-basis: 100%; width: 100%; min-width: 0 !important; }
    .opportunity-board-page .column_drag { min-height: 120px; }
}
</style>
<div>
    <div class="row text-end mb-3">
        <div class="col">
        </div>
    </div>
    <div class="skolling">
          <div class="board">
            @foreach($opportunity_status as $status)
              <div class="column p-0" >
                 <div class="card p-0 m-0">
                     <div class="card-header bg-white drag-header">
                        <h3>{{$status->status_name}}</h3>
                        <div class="apprtunity-price">{{$all_opportunities->where('status', $status->id)->count()}} {{\Illuminate\Support\Str::plural('opportunity', $all_opportunities->where('status', $status->id)->count())}}</div>
                        <div class="flex anulprice">
                         <p class="same"> Annualized Value</p>
                         <!-- <p class="dam">₹ {{$all_opportunities->where('status', $status->id)->sum('amount')}}</p> -->
                         <p class="dam">₹ {{number_format($all_opportunities->where('status', $status->id)->sum('amount'), 0, '.', ',')}}</p>
                       </div>
                     </div>
                     <div class="card-body bgrid column_drag" data-status="{{$status->id}}">
                         @foreach($all_opportunities  as $all_opportunity)
                            @if($all_opportunity->status == $status->id)
                          <div class="card_drag card bg-white p-0" draggable="true" data-id="{{$all_opportunity->id}}">
                            <div class="card-header p-2">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                     <div class="header_image">
                                        <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="text">
                                        <h5 class="mb-0">{{optional($all_opportunity->lead)->company_name ?? 'Unknown lead'}}</h5>
                                        <p>({{$all_opportunity->note}})</p>
                                     </div>
                                </div>
                                 <button type="button" class="hoverbtn btn" onclick="getOpportunitydata('{{$all_opportunity->id}}')"> <img src="{{ url('/').'/'.asset('assets/img')}}/ph_note-pencil-fill.svg"></button>
                                 <button type="button" class="hoverbtn2 btn" onclick="window.location='{{ route('leads.show', $all_opportunity->lead->id) }}'"><i style="color: #263e65;" class="material-icons">visibility</i></button>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-row align-items-center">
                                     <div class="img_dd">
                                         <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="data-ss">
                                         <h5>₹{{number_format($all_opportunity->amount, 0, '.', ',')}}</h5>
                                         <p>{{$all_opportunity->confidence}}% confidence · {{date("d M Y",strtotime($all_opportunity->estimated_close_date))}}</p>
                                         <p class="mt-1">Owner: <b>{{optional($all_opportunity->assignUser)->name ?? 'Unassigned'}}</b></p>
                                         
                                     </div>

                                </div>
                            </div>
                           
                           
                          </div>
                          @endif
                         @endforeach
                     </div>
                 </div>
              </div>
              @endforeach
{{--
              <div class="column p-0" >
                 <div class="card p-0 m-0">
                     <div class="card-header bg-white drag-header">
                        <h3>Demo Book</h3>
                        <div class="apprtunity-price"> {{count($demo_book_opportunities)}} OPPORTUNITY</div>
                        <div class="flex anulprice">
                         <p class="same"> Annualized Value</p>
                         <p class="dam">₹ {{$demo_book_sum}}</p>
                       </div>
                     </div>
                     <div class="card-body bgrid column_drag" data-status="demo_book">
                         @foreach($demo_book_opportunities  as $demo_book_opportunity)
                          <div class="card_drag card bg-white p-0" draggable="true" data-id="{{$demo_book_opportunity->id}}">
                            <div class="card-header p-2">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                     <div class="header_image">
                                        <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="text">
                                        <p>{{$demo_book_opportunity->note}}</p>
                                     </div>
                                </div>
                                 <button type="button" class="hoverbtn btn" onclick="getOpportunitydata('{{$demo_book_opportunity->id}}')"> <img src="{{ url('/').'/'.asset('assets/img')}}/ph_note-pencil-fill.svg"></button>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-row align-items-center">
                                     <div class="img_dd">
                                         <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="data-ss">
                                         <h5>₹{{$demo_book_opportunity->amount}}</h5>
                                         <p>{{$demo_book_opportunity->confidence}}% on {{date("d/m/Y",strtotime($demo_book_opportunity->estimated_close_date))}}</p>
                                     </div>
                                </div>
                            </div>
                          
                           
                          </div>
                         @endforeach
                     </div>
                 </div>
              </div>

              <div class="column p-0">
                 <div class="card p-0 m-0">
                     <div class="card-header bg-white drag-header">
                        <h3>Demo Completed</h3>
                        <div class="apprtunity-price"> {{count($demo_completed_opportunities)}} OPPORTUNITY</div>
                        <div class="flex anulprice">
                         <p class="same"> Annualized Value</p>
                         <p class="dam">₹ {{$demo_completed_sum}}</p>
                       </div>
                     </div>
                     <div class="card-body bgrid column_drag" data-status="demo_completed">
                         @foreach($demo_completed_opportunities  as $demo_completed_opportunity)
                          <div class="card_drag card bg-white p-0" draggable="true" data-id="{{$demo_completed_opportunity->id}}">
                            <div class="card-header p-2">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                     <div class="header_image">
                                        <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="text">
                                        <p>{{$demo_completed_opportunity->note}}</p>
                                     </div>
                                </div>
                                 <button type="button" class="hoverbtn btn" onclick="getOpportunitydata('{{$demo_completed_opportunity->id}}')"> <img src="{{ url('/').'/'.asset('assets/img')}}/ph_note-pencil-fill.svg"></button>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-row align-items-center">
                                     <div class="img_dd">
                                         <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="data-ss">
                                         <h5>₹{{$demo_completed_opportunity->amount}}</h5>
                                         <p>{{$demo_completed_opportunity->confidence}}% on {{date("d/m/Y",strtotime($demo_completed_opportunity->estimated_close_date))}}</p>
                                     </div>
                                </div>
                            </div>
                          
                           
                          </div>
                         @endforeach
                     </div>
                 </div>
              </div>

               <div class="column p-0">
                 <div class="card p-0 m-0">
                     <div class="card-header bg-white drag-header">
                        <h3>Negotiating</h3>
                        <div class="apprtunity-price"> {{count($negotiating_opportunities)}} OPPORTUNITY</div>
                        <div class="flex anulprice">
                         <p class="same"> Annualized Value</p>
                         <p class="dam">₹ {{$negotiating_sum}}</p>
                       </div>
                     </div>
                     <div class="card-body bgrid column_drag" data-status="negotiating">
                         @foreach($negotiating_opportunities  as $negotiating_opportunity)
                          <div class="card_drag card bg-white p-0" draggable="true" data-id="{{$negotiating_opportunity->id}}">
                            <div class="card-header p-2">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                     <div class="header_image">
                                        <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="text">
                                        <p>{{$negotiating_opportunity->note}}</p>
                                     </div>
                                </div>
                                 <button type="button" class="hoverbtn btn" onclick="getOpportunitydata('{{$negotiating_opportunity->id}}')"> <img src="{{ url('/').'/'.asset('assets/img')}}/ph_note-pencil-fill.svg"></button>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-row align-items-center">
                                     <div class="img_dd">
                                         <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="data-ss">
                                         <h5>₹{{$negotiating_opportunity->amount}}</h5>
                                         <p>{{$negotiating_opportunity->confidence}}% on {{date("d/m/Y",strtotime($negotiating_opportunity->estimated_close_date))}}</p>
                                     </div>
                                </div>
                            </div>
                          
                           
                          </div>
                         @endforeach
                     </div>
                 </div>
              </div>

              <div class="column p-0">
                 <div class="card p-0 m-0">
                     <div class="card-header bg-white drag-header">
                        <h3>Interested</h3>
                        <div class="apprtunity-price"> {{count($interested_opportunities)}} OPPORTUNITY</div>
                        <div class="flex anulprice">
                         <p class="same"> Annualized Value</p>
                         <p class="dam">₹ {{$interested_sum}}</p>
                       </div>
                     </div>
                     <div class="card-body bgrid column_drag" data-status="interested">
                         @foreach($interested_opportunities  as $interested_opportunity)
                          <div class="card_drag card bg-white p-0" draggable="true" data-id="{{$interested_opportunity->id}}">
                            <div class="card-header p-2">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                     <div class="header_image">
                                        <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="text">
                                        <p>{{$interested_opportunity->note}}</p>
                                     </div>
                                </div>
                                 <button type="button" class="hoverbtn btn" onclick="getOpportunitydata('{{$interested_opportunity->id}}')"> <img src="{{ url('/').'/'.asset('assets/img')}}/ph_note-pencil-fill.svg"></button>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-row align-items-center">
                                     <div class="img_dd">
                                         <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="data-ss">
                                         <h5>₹{{$interested_opportunity->amount}}</h5>
                                         <p>{{$interested_opportunity->confidence}}% on {{date("d/m/Y",strtotime($interested_opportunity->estimated_close_date))}}</p>
                                     </div>
                                </div>
                            </div>
                          
                          </div>
                         @endforeach
                     </div>
                 </div>
              </div>

                <div class="column p-0">
                 <div class="card p-0 m-0">
                     <div class="card-header bg-white drag-header">
                        <h3>Not Interested</h3>
                        <div class="apprtunity-price"> {{count($not_interested_opportunities)}} OPPORTUNITY</div>
                        <div class="flex anulprice">
                         <p class="same"> Annualized Value</p>
                         <p class="dam">₹ {{$not_interested_sum}}</p>
                       </div>
                     </div>
                     <div class="card-body bgrid column_drag" data-status="not_interested">
                         @foreach($not_interested_opportunities  as $not_interested_opportunity)
                          <div class="card_drag card bg-white p-0" draggable="true" data-id="{{$not_interested_opportunity->id}}">
                            <div class="card-header p-2">
                                <div class="d-flex flex-row justify-content-start align-items-center">
                                     <div class="header_image">
                                        <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="text">
                                        <p>{{$not_interested_opportunity->note}}</p>
                                     </div>
                                </div>
                                 <button type="button" class="hoverbtn btn" onclick="getOpportunitydata('{{$not_interested_opportunity->id}}')"> <img src="{{ url('/').'/'.asset('assets/img')}}/ph_note-pencil-fill.svg"></button>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-row align-items-center">
                                     <div class="img_dd">
                                         <img src="{{ url('/').'/'.asset('assets/img')}}/circaldrag.svg">
                                     </div>
                                     <div class="data-ss">
                                         <h5>₹{{$not_interested_opportunity->amount}}</h5>
                                         <p>{{$not_interested_opportunity->confidence}}% on {{date("d/m/Y",strtotime($not_interested_opportunity->estimated_close_date))}}</p>
                                     </div>
                                </div>
                            </div>
                          
                          </div>
                         @endforeach
                     </div>
                 </div>
              </div>
             --}}

               
          </div>
          </div>
      </div>
</div>
<script>
    var draggedCard = null;

    document.querySelectorAll('.card_drag').forEach(card => {
        card.addEventListener('dragstart', () => {
            draggedCard = card;
            card.style.opacity = '0.5';
        });

        card.addEventListener('dragend', () => {
            draggedCard.style.opacity = '1';
            draggedCard = null;
        });
    });

    document.querySelectorAll('.column_drag').forEach(column => {
        column.addEventListener('dragover', (e) => {
            e.preventDefault();
        });

        column.addEventListener('drop', () => {
            if (draggedCard) {
                column.appendChild(draggedCard);

                const card_id = draggedCard.getAttribute('data-id');
                const new_status = column.getAttribute('data-status');

                //console.log(`Card ID: ${card_id}, New Status: ${newStatus}`);

                $.post("{{ route('lead-opportunities.updateCardStatus') }}", {card_id:card_id,new_status:new_status }, function(response){
                    getCardData();
                    setTimeout(() => {
                        smoothCounter('dam', 700);
                    }, 500);
                }); 

               
            }
        });
    });
    
    function smoothCounter(className, duration) {
        const elements = document.getElementsByClassName(className);

        Array.from(elements).forEach((el) => {
            const fullText = el.textContent.trim();
            const endValue = parseInt(fullText.replace(/[^\d]/g, ''), 10);
            if (Number.isNaN(endValue)) return;
            const startValue = 0;
            const startTime = performance.now();

            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const value = Math.floor(startValue + progress * (endValue - startValue));
                el.textContent = `₹ ${value.toLocaleString('en-IN')}`;

                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }

            requestAnimationFrame(update);
        });
    }

    // Run it after the page loads
    window.addEventListener('DOMContentLoaded', () => {
        smoothCounter('dam', 1000); // 1000 ms = 1 second
    });

</script>
