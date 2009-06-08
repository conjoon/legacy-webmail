Ext.namespace("Ext.ux.grid.livegrid");Ext.ux.grid.livegrid.GridPanel=Ext.extend(Ext.grid.GridPanel,{onRender:function(B,A){Ext.ux.grid.livegrid.GridPanel.superclass.onRender.call(this,B,A);var C=this.getStore();if(C._autoLoad===true){delete C._autoLoad;C.load()}},walkCells:function(H,C,F,E,D){var G=this.store;var A=G.getCount;G.getCount=G.getTotalCount;var B=Ext.ux.grid.livegrid.GridPanel.superclass.walkCells.call(this,H,C,F,E,D);G.getCount=A;return B}});Ext.namespace("Ext.ux.grid.livegrid");Ext.ux.grid.livegrid.GridView=function(A){this.addEvents({beforebuffer:true,buffer:true,bufferfailure:true,cursormove:true});this.horizontalScrollOffset=17;this._checkEmptyBody=true;Ext.apply(this,A);this.templates={};this.templates.master=new Ext.Template('<div class="x-grid3" hidefocus="true"><div class="ext-ux-livegrid-liveScroller"><div></div></div>','<div class="x-grid3-viewport"">','<div class="x-grid3-header"><div class="x-grid3-header-inner"><div class="x-grid3-header-offset" style="{ostyle}">{header}</div></div><div class="x-clear"></div></div>','<div class="x-grid3-scroller" style="overflow-y:hidden !important;"><div class="x-grid3-body" style="{bstyle}">{body}</div><a href="#" class="x-grid3-focus" tabIndex="-1"></a></div>',"</div>",'<div class="x-grid3-resize-marker">&#160;</div>','<div class="x-grid3-resize-proxy">&#160;</div>',"</div>");this._gridViewSuperclass=Ext.ux.grid.livegrid.GridView.superclass;this._gridViewSuperclass.constructor.call(this)};Ext.extend(Ext.ux.grid.livegrid.GridView,Ext.grid.GridView,{_maskIndex:20001,hdHeight:0,rowClipped:0,liveScroller:null,liveScrollerInset:null,rowHeight:-1,visibleRows:1,lastIndex:-1,lastRowIndex:0,lastScrollPos:0,rowIndex:0,isBuffering:false,requestQueue:-1,loadMask:false,isPrebuffering:false,reset:function(C){if(C===false){this.ds.modified=[];this.rowIndex=0;this.lastScrollPos=0;this.lastRowIndex=0;this.lastIndex=0;this.adjustVisibleRows();this.adjustScrollerPos(-this.liveScroller.dom.scrollTop,true);this.showLoadMask(false);this.refresh(true);this.fireEvent("cursormove",this,0,Math.min(this.ds.totalLength,this.visibleRows-this.rowClipped),this.ds.totalLength);return false}else{var B={};var A=this.ds.sortInfo;if(A){B={dir:A.direction,sort:A.field}}return this.ds.load({params:B})}},renderUI:function(){var A=this.grid;var B=A.enableDragDrop||A.enableDrag;A.enableDragDrop=false;A.enableDrag=false;this._gridViewSuperclass.renderUI.call(this);var A=this.grid;A.enableDragDrop=B;A.enableDrag=B;if(B){this.dragZone=new Ext.ux.grid.livegrid.DragZone(A,{ddGroup:A.ddGroup||"GridDD"})}if(this.loadMask){this.loadMask=new Ext.LoadMask(this.mainBody.dom.parentNode.parentNode,this.loadMask)}},init:function(A){this._gridViewSuperclass.init.call(this,A);A.on("expand",this._onExpand,this)},initData:function(B,A){if(this.ds){this.ds.un("bulkremove",this.onBulkRemove,this);this.ds.un("beforeload",this.onBeforeLoad,this)}if(B){B.on("bulkremove",this.onBulkRemove,this);B.on("beforeload",this.onBeforeLoad,this)}this._gridViewSuperclass.initData.call(this,B,A)},renderBody:function(){var A=this.renderRows(0,this.visibleRows-1);return this.templates.body.apply({rows:A})},doRender:function(C,B,E,A,D,F){return this._gridViewSuperclass.doRender.call(this,C,B,E,A+this.ds.bufferRange[0],D,F)},initElements:function(){var D=Ext.Element;var B=this.grid.getGridEl().dom.firstChild;var A=B.childNodes;this.el=new D(B);this.mainWrap=new D(A[1]);this.liveScroller=new D(A[0]);this.liveScrollerInset=this.liveScroller.dom.firstChild;this.liveScroller.on("scroll",this.onLiveScroll,this,{buffer:this.scrollDelay});var C=this.mainWrap.dom.firstChild;this.mainHd=new D(C);this.hdHeight=C.offsetHeight;this.innerHd=this.mainHd.dom.firstChild;this.scroller=new D(this.mainWrap.dom.childNodes[1]);if(this.forceFit){this.scroller.setStyle("overflow-x","hidden")}this.mainBody=new D(this.scroller.dom.firstChild);this.mainBody.on("mousewheel",this.handleWheel,this);this.focusEl=new D(this.scroller.dom.childNodes[1]);this.focusEl.swallowEvent("click",true);this.resizeMarker=new D(A[2]);this.resizeProxy=new D(A[3])},layout:function(){if(!this.mainBody){return }var E=this.grid;var G=E.getGridEl(),I=this.cm,B=E.autoExpandColumn,A=this;var C=G.getSize(true);var H=C.width;if(H<20||C.height<20){return }if(E.autoHeight){this.scroller.dom.style.overflow="visible";if(Ext.isWebKit){this.scroller.dom.style.position="static"}}else{this.el.setSize(C.width,C.height);var F=this.mainHd.getHeight();var D=C.height-(F);this.scroller.setSize(H,D);if(this.innerHd){this.innerHd.style.width=(H)+"px"}}this.liveScroller.dom.style.top=this.hdHeight+"px";if(this.forceFit){if(this.lastViewWidth!=H){this.fitColumns(false,false);this.lastViewWidth=H}}else{this.autoExpand()}this.adjustVisibleRows();this.adjustBufferInset();this.onLayout(H,D)},removeRow:function(A){Ext.removeNode(this.getRow(A))},removeRows:function(C,A){var B=this.mainBody.dom;for(var D=C;D<=A;D++){Ext.removeNode(B.childNodes[C])}},_onExpand:function(A){this.adjustVisibleRows();this.adjustBufferInset();this.adjustScrollerPos(this.rowHeight*this.rowIndex,true)},onColumnMove:function(A,C,B){this.indexMap=null;this.replaceLiveRows(this.rowIndex,true);this.updateHeaders();this.updateHeaderSortState();this.afterMove(B)},onColumnWidthUpdated:function(C,A,B){this.adjustVisibleRows();this.adjustBufferInset()},onAllColumnWidthsUpdated:function(A,B){this.adjustVisibleRows();this.adjustBufferInset()},onRowSelect:function(A){if(A<this.rowIndex||A>this.rowIndex+this.visibleRows){return }this.addRowClass(A,this.selectedRowClass)},onRowDeselect:function(A){if(A<this.rowIndex||A>this.rowIndex+this.visibleRows){return }this.removeRowClass(A,this.selectedRowClass)},onClear:function(){this.reset(false)},onBulkRemove:function(L,M){var H=null;var J=0;var O=0;var K=M.length;var A=false;var I=false;var F=0;if(K==0){return }var C=this.rowIndex;var B=0;var E=0;var D=0;for(var G=0;G<K;G++){H=M[G][0];J=M[G][1];O=(J!=Number.MIN_VALUE&&J!=Number.MAX_VALUE)?J+this.ds.bufferRange[0]:J;if(O<this.rowIndex){B++}else{if(O>=this.rowIndex&&O<=this.rowIndex+(this.visibleRows-1)){D++}else{if(O>=this.rowIndex+this.visibleRows){E++}}}this.fireEvent("beforerowremoved",this,O,H);this.fireEvent("rowremoved",this,O,H)}var N=this.ds.totalLength;this.rowIndex=Math.max(0,Math.min(this.rowIndex-B,N-(this.visibleRows-1)));this.lastRowIndex=this.rowIndex;this.adjustScrollerPos(-(B*this.rowHeight),true);this.updateLiveRows(this.rowIndex,true);this.adjustBufferInset();this.processRows(0,undefined,false)},onRemove:function(C,A,B){this.onBulkRemove(C,[[A,B]])},onAdd:function(B,C,G){if(this._checkEmptyBody){if(this.mainBody.dom.innerHTML=="&nbsp;"){this.mainBody.dom.innerHTML=""}this._checkEmptyBody=false}var F=C.length;if(G==Number.MAX_VALUE||G==Number.MIN_VALUE){this.fireEvent("beforerowsinserted",this,G,G);if(G==Number.MIN_VALUE){this.rowIndex=this.rowIndex+F;this.lastRowIndex=this.rowIndex;this.adjustBufferInset();this.adjustScrollerPos(this.rowHeight*F,true);this.fireEvent("rowsinserted",this,G,G,F);this.processRows(0,undefined,false);this.fireEvent("cursormove",this,this.rowIndex,Math.min(this.ds.totalLength,this.visibleRows-this.rowClipped),this.ds.totalLength);return }this.adjustBufferInset();this.fireEvent("rowsinserted",this,G,G,F);return }var A=G+this.ds.bufferRange[0];var E=A+(F-1);var H=this.getRows().length;var D=0;var I=0;if(A>this.rowIndex+(this.visibleRows-1)){this.fireEvent("beforerowsinserted",this,A,E);this.fireEvent("rowsinserted",this,A,E,F);this.adjustVisibleRows();this.adjustBufferInset()}else{if(A>=this.rowIndex&&A<=this.rowIndex+(this.visibleRows-1)){D=G;I=G+(F-1);this.lastRowIndex=this.rowIndex;this.rowIndex=(A>this.rowIndex)?this.rowIndex:A;this.insertRows(B,D,I);if(this.lastRowIndex!=this.rowIndex){this.fireEvent("cursormove",this,this.rowIndex,Math.min(this.ds.totalLength,this.visibleRows-this.rowClipped),this.ds.totalLength)}this.adjustVisibleRows();this.adjustBufferInset()}else{if(A<this.rowIndex){this.fireEvent("beforerowsinserted",this,A,E);this.rowIndex=this.rowIndex+F;this.lastRowIndex=this.rowIndex;this.adjustVisibleRows();this.adjustBufferInset();this.adjustScrollerPos(this.rowHeight*F,true);this.fireEvent("rowsinserted",this,A,E,F);this.processRows(0,undefined,true);this.fireEvent("cursormove",this,this.rowIndex,Math.min(this.ds.totalLength,this.visibleRows-this.rowClipped),this.ds.totalLength)}}}},onBeforeLoad:function(B,C){C.params=C.params||{};var A=Ext.apply;A(C,{scope:this,callback:function(){this.reset(false)}});A(C.params,{start:0,limit:this.ds.bufferSize});return true},onLoad:function(C,B,A){this.adjustBufferInset()},onDataChange:function(A){this.updateHeaderSortState()},liveBufferUpdate:function(A,B,D){if(D===true){this.adjustBufferInset();this.fireEvent("buffer",this,this.ds,this.rowIndex,Math.min(this.ds.totalLength,this.visibleRows-this.rowClipped),this.ds.totalLength,B);this.isBuffering=false;this.isPrebuffering=false;this.showLoadMask(false);this.grid.selModel.replaceSelections(A);if(this.isInRange(this.rowIndex)){this.replaceLiveRows(this.rowIndex,B.forceRepaint)}else{this.updateLiveRows(this.rowIndex)}if(this.requestQueue>=0){var C=this.requestQueue;this.requestQueue=-1;this.updateLiveRows(C)}return }else{this.fireEvent("bufferfailure",this,this.ds,B)}this.requestQueue=-1;this.isBuffering=false;this.isPrebuffering=false;this.showLoadMask(false)},handleWheel:function(A){if(this.rowHeight==-1){A.stopEvent();return }var B=A.getWheelDelta();this.adjustScrollerPos(-(B*this.rowHeight));A.stopEvent()},onLiveScroll:function(){var A=this.liveScroller.dom.scrollTop;var B=Math.floor((A)/this.rowHeight);this.rowIndex=B;if(B==this.lastRowIndex){return }this.updateLiveRows(B);this.lastScrollPos=this.liveScroller.dom.scrollTop},refreshRow:function(A){var D=this.ds,C;if(typeof A=="number"){C=A;A=D.getAt(C)}else{C=D.indexOf(A)}var B=C+this.ds.bufferRange[0];if(B<this.rowIndex||B>=this.rowIndex+this.visibleRows){this.fireEvent("rowupdated",this,B,A);return }this.insertRows(D,C,C,true);this.fireEvent("rowupdated",this,B,A)},processRows:function(I,H,E){H=H||!this.grid.stripeRows;I=0;var N=this.getRows();var K=" x-grid3-row-alt ";var J=this.rowIndex;var G=0;var A=this.grid.selModel.selections;var B=this.ds;var M=null;for(var D=I,F=N.length;D<F;D++){G=D+J;M=N[D];M.rowIndex=G;if(E!==false){if(this.grid.selModel.isSelected(this.ds.getAt(G))===true){this.addRowClass(G,"x-grid3-row-selected")}else{this.removeRowClass(G,"x-grid3-row-selected")}this.fly(M).removeClass("x-grid3-row-over")}if(!H){var C=((G+1)%2==0);var L=(" "+M.className+" ").indexOf(K)!=-1;if(C==L){continue}if(C){M.className+=" x-grid3-row-alt"}else{M.className=M.className.replace("x-grid3-row-alt","")}}}},insertRows:function(E,B,M,L){var A=B+this.ds.bufferRange[0];var F=M+this.ds.bufferRange[0];if(!L){this.fireEvent("beforerowsinserted",this,A,F)}if(L!==true&&(this.getRows().length+(M-B))>=this.visibleRows){this.removeRows((this.visibleRows-1)-(M-B),this.visibleRows-1)}else{if(L){this.removeRows(A-this.rowIndex,F-this.rowIndex)}}var G=(B==M)?M:Math.min(M,(this.rowIndex-this.ds.bufferRange[0])+(this.visibleRows-1));var D=this.renderRows(B,G);var I=this.getRow(A);if(I){Ext.DomHelper.insertHtml("beforeBegin",I,D)}else{Ext.DomHelper.insertHtml("beforeEnd",this.mainBody.dom,D)}if(L===true){var K=this.getRows();var J=this.rowIndex;for(var C=0,H=K.length;C<H;C++){K[C].rowIndex=J+C}}if(!L){this.fireEvent("rowsinserted",this,A,F,(F-A)+1);this.processRows(0,undefined,true)}},getRow:function(A){if(A-this.rowIndex<0){return null}return this.getRows()[A-this.rowIndex]},getCell:function(B,A){var B=this.getRow(B);return B?B.getElementsByTagName("td")[A]:null},focusCell:function(D,A,C){var B=this.ensureVisible(D,A,C);if(!B){return }this.focusEl.setXY(B);if(Ext.isGecko){this.focusEl.focus()}else{this.focusEl.focus.defer(1,this.focusEl)}},ensureVisible:function(K,C,B){if(typeof K!="number"){K=K.rowIndex}if(K<0||K>=this.ds.totalLength){return }C=(C!==undefined?C:0);var H=K-this.rowIndex;if(this.rowClipped&&K==this.rowIndex+this.visibleRows-1){this.adjustScrollerPos(this.rowHeight)}else{if(K>=this.rowIndex+this.visibleRows){this.adjustScrollerPos(((K-(this.rowIndex+this.visibleRows))+1)*this.rowHeight)}else{if(K<=this.rowIndex){this.adjustScrollerPos((H)*this.rowHeight)}}}var G=this.getRow(K),D;if(!G){return }if(!(B===false&&C===0)){while(this.cm.isHidden(C)){C++}D=this.getCell(K,C)}var J=this.scroller.dom;if(B!==false){var I=parseInt(D.offsetLeft,10);var F=I+D.offsetWidth;var E=parseInt(J.scrollLeft,10);var A=E+J.clientWidth;if(I<E){J.scrollLeft=I}else{if(F>A){J.scrollLeft=F-J.clientWidth}}}return D?Ext.fly(D).getXY():[J.scrollLeft+this.el.getX(),Ext.fly(G).getY()]},isRecordRendered:function(A){var B=this.ds.indexOf(A);if(B>=this.rowIndex&&B<this.rowIndex+this.visibleRows){return true}return false},isInRange:function(B){var A=Math.min(this.ds.totalLength-1,B+(this.visibleRows-1));return(B>=this.ds.bufferRange[0])&&(A<=this.ds.bufferRange[1])},getPredictedBufferIndex:function(A,B,C){if(!B){if(A+this.ds.bufferSize>=this.ds.totalLength){return this.ds.totalLength-this.ds.bufferSize}return Math.max(0,(A+this.visibleRows)-Math.round(this.ds.bufferSize/2))}if(!C){return Math.max(0,(A-this.ds.bufferSize)+this.visibleRows)}if(C){return Math.max(0,Math.min(A,this.ds.totalLength-this.ds.bufferSize))}},updateLiveRows:function(G,H,D){var J=this.isInRange(G);if(this.isBuffering){if(this.isPrebuffering){if(J){this.replaceLiveRows(G,H)}else{this.showLoadMask(true)}}this.fireEvent("cursormove",this,G,Math.min(this.ds.totalLength,this.visibleRows-this.rowClipped),this.ds.totalLength);this.requestQueue=G;return }var E=this.lastIndex;this.lastIndex=G;var J=this.isInRange(G);var I=false;if(J&&D!==true){this.replaceLiveRows(G,H);this.fireEvent("cursormove",this,G,Math.min(this.ds.totalLength,this.visibleRows-this.rowClipped),this.ds.totalLength);if(G>E){I=true;var K=this.ds.totalLength;if(G+this.visibleRows+this.nearLimit<=this.ds.bufferRange[1]){return }if(this.ds.bufferRange[1]+1>=K){return }}else{if(G<E){I=false;if(this.ds.bufferRange[0]<=0){return }if(G-this.nearLimit>this.ds.bufferRange[0]){return }}else{return }}this.isPrebuffering=true}this.isBuffering=true;var B=this.getPredictedBufferIndex(G,J,I);if(!J){this.showLoadMask(true)}this.ds.suspendEvents();var F=this.ds.sortInfo;var C={};if(this.ds.lastOptions){Ext.apply(C,this.ds.lastOptions.params)}C.start=B;C.limit=this.ds.bufferSize;if(F){C.dir=F.direction;C.sort=F.field}var A={forceRepaint:H,callback:this.liveBufferUpdate,scope:this,params:C,suspendLoadEvent:true};this.fireEvent("beforebuffer",this,this.ds,G,Math.min(this.ds.totalLength,this.visibleRows-this.rowClipped),this.ds.totalLength,A);this.ds.load(A);this.ds.resumeEvents()},showLoadMask:function(A){if(!this.loadMask){return }if(A){this.loadMask.show();this.liveScroller.setStyle("zIndex",this._maskIndex)}else{this.loadMask.hide();this.liveScroller.setStyle("zIndex",1)}},replaceLiveRows:function(H,G,C){var D=H-this.lastRowIndex;if(D==0&&G!==true){return }var A=D>0;D=Math.abs(D);var B=this.ds.bufferRange;var I=H-B[0];var E=Math.min(I+this.visibleRows-1,B[1]-B[0]);if(D>=this.visibleRows||D==0){this.mainBody.update(this.renderRows(I,E))}else{if(A){this.removeRows(0,D-1);if(I+this.visibleRows-D<=B[1]-B[0]){var F=this.renderRows(I+this.visibleRows-D,E);Ext.DomHelper.insertHtml("beforeEnd",this.mainBody.dom,F)}}else{this.removeRows(this.visibleRows-D,this.visibleRows-1);var F=this.renderRows(I,I+D-1);Ext.DomHelper.insertHtml("beforeBegin",this.mainBody.dom.firstChild,F)}}if(C!==false){this.processRows(0,undefined,true)}this.lastRowIndex=H},adjustBufferInset:function(){var B=this.liveScroller.dom;var D=this.grid,E=D.store;var H=D.getGridEl();var C=H.getSize().width;var F=(E.totalLength==this.visibleRows-this.rowClipped)?0:Math.max(0,E.totalLength-(this.visibleRows-this.rowClipped));if(F==0){this.scroller.setWidth(C);B.style.display="none";return }else{this.scroller.setWidth(C-this.scrollOffset);B.style.display=""}var G=this.cm.getTotalWidth()+this.scrollOffset>C;var A=B.parentNode.offsetHeight+((E.totalLength>0&&G)?-this.horizontalScrollOffset:0)-this.hdHeight;B.style.height=Math.max(A,this.horizontalScrollOffset*2)+"px";if(this.rowHeight==-1){return }this.liveScrollerInset.style.height=(F==0?0:A+(F*this.rowHeight))+"px"},adjustVisibleRows:function(){if(this.rowHeight==-1){if(this.getRows()[0]){this.rowHeight=this.getRows()[0].offsetHeight;if(this.rowHeight<=0){this.rowHeight=-1;return }}else{return }}var E=this.grid,C=E.store;var F=E.getGridEl();var H=this.cm;var J=F.getSize();var B=J.width;var D=J.height;var G=B-this.scrollOffset;if(H.getTotalWidth()>G){D-=this.horizontalScrollOffset}D-=this.mainHd.getHeight();var I=C.totalLength||0;var A=Math.max(1,Math.floor(D/this.rowHeight));this.rowClipped=0;if(I>A&&this.rowHeight/3<(D-(A*this.rowHeight))){A=Math.min(A+1,I);this.rowClipped=1}if(this.visibleRows==A){return }this.visibleRows=A;if(this.isBuffering&&!this.isPrebuffering){return }if(this.rowIndex+(A-this.rowClipped)>I){this.rowIndex=Math.max(0,I-(A-this.rowClipped));this.lastRowIndex=this.rowIndex}this.updateLiveRows(this.rowIndex,true)},adjustScrollerPos:function(D,A){if(D==0){return }var C=this.liveScroller;var B=C.dom;if(A===true){C.un("scroll",this.onLiveScroll,this)}this.lastScrollPos=B.scrollTop;B.scrollTop+=D;if(A===true){B.scrollTop=B.scrollTop;C.on("scroll",this.onLiveScroll,this,{buffer:this.scrollDelay})}}});Ext.namespace("Ext.ux.grid.livegrid");Ext.ux.grid.livegrid.JsonReader=function(A,B){Ext.ux.grid.livegrid.JsonReader.superclass.constructor.call(this,A,B)};Ext.extend(Ext.ux.grid.livegrid.JsonReader,Ext.data.JsonReader,{readRecords:function(D){var B=this.meta;if(!this.ef&&B.versionProperty){this.getVersion=this.getJsonAccessor(B.versionProperty)}if(!this.__readRecords){this.__readRecords=Ext.ux.grid.livegrid.JsonReader.superclass.readRecords}var C=this.__readRecords.call(this,D);if(B.versionProperty){var A=this.getVersion(D);C.version=(A===undefined||A==="")?null:A}return C}});Ext.namespace("Ext.ux.grid.livegrid");Ext.ux.grid.livegrid.RowSelectionModel=function(A){this.addEvents({selectiondirty:true});Ext.apply(this,A);this.pendingSelections={};Ext.ux.grid.livegrid.RowSelectionModel.superclass.constructor.call(this)};Ext.extend(Ext.ux.grid.livegrid.RowSelectionModel,Ext.grid.RowSelectionModel,{initEvents:function(){Ext.ux.grid.livegrid.RowSelectionModel.superclass.initEvents.call(this);this.grid.view.on("rowsinserted",this.onAdd,this);this.grid.store.on("selectionsload",this.onSelectionsLoad,this)},onRemove:function(B,D,G){var A=this.getPendingSelections();var C=A.length;var F=false;if(D==Number.MIN_VALUE||D==Number.MAX_VALUE){if(G){if(this.isIdSelected(G.id)&&D==Number.MIN_VALUE){this.shiftSelections(this.grid.store.bufferRange[1],-1)}this.selections.remove(G);F=true}if(D==Number.MIN_VALUE){this.clearPendingSelections(0,this.grid.store.bufferRange[0])}else{this.clearPendingSelections(this.grid.store.bufferRange[1])}if(C!=0){this.fireEvent("selectiondirty",this,D,1)}}else{F=this.isIdSelected(G.id);if(!F){return }this.selections.remove(G);if(C!=0){var H=A[0];var E=A[C-1];if(D<=E||D<=H){this.shiftSelections(D,-1);this.fireEvent("selectiondirty",this,D,1)}}}if(F){this.fireEvent("selectionchange",this)}},onAdd:function(G,E,D,B){var A=this.getPendingSelections();var H=A.length;if((E==Number.MIN_VALUE||E==Number.MAX_VALUE)){if(E==Number.MIN_VALUE){this.clearPendingSelections(0,this.grid.store.bufferRange[0]);this.shiftSelections(this.grid.store.bufferRange[1],B)}else{this.clearPendingSelections(this.grid.store.bufferRange[1])}if(H!=0){this.fireEvent("selectiondirty",this,E,r)}return }var F=A[0];var C=A[H-1];var I=E;if(I<=C||I<=F){this.fireEvent("selectiondirty",this,I,B);this.shiftSelections(I,B)}},shiftSelections:function(L,C){var H=0;var K=0;var B={};var D=this.grid.store;var I=L-D.bufferRange[0];var F=0;var M=this.grid.store.totalLength;var E=null;var A=this.getPendingSelections();var J=A.length;if(J==0){return }for(var G=0;G<J;G++){H=A[G];if(H<L){continue}K=H+C;F=I+C;if(K>=M){break}E=D.getAt(F);if(E){this.selections.add(E)}else{B[K]=true}}this.pendingSelections=B},onSelectionsLoad:function(C,B,A){this.replaceSelections(B)},hasNext:function(){return this.last!==false&&(this.last+1)<this.grid.store.getTotalCount()},getCount:function(){return this.selections.length+this.getPendingSelections().length},isSelected:function(A){if(typeof A=="number"){var B=A;A=this.grid.store.getAt(B);if(!A){var D=this.getPendingSelections().indexOf(B);if(D!=-1){return true}return false}}var C=A;return(C&&this.selections.key(C.id)?true:false)},deselectRecord:function(A,C){if(this.locked){return }var E=this.selections.key(A.id);if(!E){return }var B=this.grid.store;var D=B.indexOfId(A.id);if(D==-1){D=B.findInsertIndex(A);if(D!=Number.MIN_VALUE&&D!=Number.MAX_VALUE){D+=B.bufferRange[0]}}else{delete this.pendingSelections[D]}if(this.last==D){this.last=false}if(this.lastActive==D){this.lastActive=false}this.selections.remove(A);if(!C){this.grid.getView().onRowDeselect(D)}this.fireEvent("rowdeselect",this,D,A);this.fireEvent("selectionchange",this)},deselectRow:function(B,A){if(this.locked){return }if(this.last==B){this.last=false}if(this.lastActive==B){this.lastActive=false}var C=this.grid.store.getAt(B);delete this.pendingSelections[B];if(C){this.selections.remove(C)}if(!A){this.grid.getView().onRowDeselect(B)}this.fireEvent("rowdeselect",this,B,C);this.fireEvent("selectionchange",this)},selectRow:function(B,D,A){if(this.locked||B<0||B>=this.grid.store.getTotalCount()){return }var C=this.grid.store.getAt(B);if(this.fireEvent("beforerowselect",this,B,D,C)!==false){if(!D||this.singleSelect){this.clearSelections()}if(C){this.selections.add(C);delete this.pendingSelections[B]}else{this.pendingSelections[B]=true}this.last=this.lastActive=B;if(!A){this.grid.getView().onRowSelect(B)}this.fireEvent("rowselect",this,B,C);this.fireEvent("selectionchange",this)}},clearPendingSelections:function(G,F){if(F==undefined){F=Number.MAX_VALUE}var B={};var A=this.getPendingSelections();var D=A.length;var C=0;for(var E=0;E<D;E++){C=A[E];if(C<=F&&C>=G){continue}B[C]=true}this.pendingSelections=B},replaceSelections:function(E){if(!E||E.length==0){return }var D=this.grid.store;var F=null;var I=[];var A=this.getPendingSelections();var J=A.length;var C=this.selections;var H=0;for(var G=0;G<J;G++){H=A[G];F=D.getAt(H);if(F){C.add(F);I.push(F.id);delete this.pendingSelections[H]}}var B=null;for(G=0,len=E.length;G<len;G++){F=E[G];B=F.id;if(I.indexOf(B)==-1&&C.containsKey(B)){C.add(F)}}},getPendingSelections:function(F){var D=1;var C=[];var B=0;var G=[];for(var E in this.pendingSelections){G.push(parseInt(E))}G.sort(function(I,H){if(I>H){return 1}else{if(I<H){return -1}else{return 0}}});if(!F){return G}var A=G.length;if(A==0){return[]}C[B]=[G[0],G[0]];for(var E=0,A=A-1;E<A;E++){if(G[E+1]-G[E]==1){C[B][1]=G[E+1]}else{B++;C[B]=[G[E+1],G[E+1]]}}return C},clearSelections:function(A){if(this.locked){return }if(A!==true){var D=this.grid.store;var B=this.selections;var C=-1;B.each(function(E){C=D.indexOfId(E.id);if(C!=-1){this.deselectRow(C+D.bufferRange[0])}},this);B.clear();this.pendingSelections={}}else{this.selections.clear();this.pendingSelections={}}this.last=false},selectRange:function(B,A,D){if(this.locked){return }if(!D){this.clearSelections()}if(B<=A){for(var C=B;C<=A;C++){this.selectRow(C,true)}}else{for(var C=B;C>=A;C--){this.selectRow(C,true)}}}});Ext.namespace("Ext.ux.grid.livegrid");Ext.ux.grid.livegrid.Store=function(A){A=A||{};A.remoteSort=true;this._autoLoad=A.autoLoad?true:false;A.autoLoad=false;this.addEvents("bulkremove","versionchange","beforeselectionsload","selectionsload");Ext.ux.grid.livegrid.Store.superclass.constructor.call(this,A);this.totalLength=0;this.bufferRange=[-1,-1];this.on("clear",function(){this.bufferRange=[-1,-1]},this);if(this.url&&!this.selectionsProxy){this.selectionsProxy=new Ext.data.HttpProxy({url:this.url})}};Ext.extend(Ext.ux.grid.livegrid.Store,Ext.data.Store,{version:null,insert:function(D,C){C=[].concat(C);D=D>=this.bufferSize?Number.MAX_VALUE:D;if(D==Number.MIN_VALUE||D==Number.MAX_VALUE){var B=C.length;if(D==Number.MIN_VALUE){this.bufferRange[0]+=B;this.bufferRange[1]+=B}this.totalLength+=B;this.fireEvent("add",this,C,D);return }var F=false;var G=C;if(C.length+D>=this.bufferSize){F=true;G=C.splice(0,this.bufferSize-D)}this.totalLength+=G.length;if(this.bufferRange[0]<=-1){this.bufferRange[0]=0}if(this.bufferRange[1]<(this.bufferSize-1)){this.bufferRange[1]=Math.min(this.bufferRange[1]+G.length,this.bufferSize-1)}for(var E=0,A=G.length;E<A;E++){this.data.insert(D,G[E]);G[E].join(this)}while(this.getCount()>this.bufferSize){this.data.remove(this.data.last())}this.fireEvent("add",this,G,D);if(F==true){this.fireEvent("add",this,C,Number.MAX_VALUE)}},remove:function(B,A){var C=this._getIndex(B);if(C<0){this.totalLength-=1;if(this.pruneModifiedRecords){this.modified.remove(B)}this.bufferRange[0]=Math.max(-1,this.bufferRange[0]-1);this.bufferRange[1]=Math.max(-1,this.bufferRange[1]-1);if(A!==true){this.fireEvent("remove",this,B,C)}return C}this.bufferRange[1]=Math.max(-1,this.bufferRange[1]-1);this.data.removeAt(C);if(this.pruneModifiedRecords){this.modified.remove(B)}this.totalLength-=1;if(A!==true){this.fireEvent("remove",this,B,C)}return C},_getIndex:function(A){var B=this.indexOfId(A.id);if(B<0){B=this.findInsertIndex(A)}return B},bulkRemove:function(B){var G=null;var E=[];var D=0;var A=B.length;var F=[];for(var C=0;C<A;C++){G=B[C];F[G.id]=this._getIndex(G)}for(var C=0;C<A;C++){G=B[C];this.remove(G,true);E.push([G,F[G.id]])}this.fireEvent("bulkremove",this,E)},removeAll:function(){this.totalLength=0;this.bufferRange=[-1,-1];this.data.clear();if(this.pruneModifiedRecords){this.modified=[]}this.fireEvent("clear",this)},loadRanges:function(B){var A=B.length;if(A>0&&!this.selectionsProxy.activeRequest[Ext.data.Api.READ]&&this.fireEvent("beforeselectionsload",this,B)!==false){var E=this.lastOptions.params;var F={};F.ranges=Ext.encode(B);if(E){if(E.sort){F.sort=E.sort}if(E.dir){F.dir=E.dir}}var C={};for(var D in this.lastOptions){C.i=this.lastOptions.i}C.ranges=F.ranges;this.selectionsProxy.load(F,this.reader,this.selectionsLoaded,this,C)}},loadSelections:function(A){if(A.length==0){return }this.loadRanges(A)},selectionsLoaded:function(F,B,E){if(this.checkVersionChange(F,B,E)!==false){var D=F.records;for(var C=0,A=D.length;C<A;C++){D[C].join(this)}this.fireEvent("selectionsload",this,F.records,Ext.decode(B.ranges))}else{this.fireEvent("selectionsload",this,[],Ext.decode(B.ranges))}},checkVersionChange:function(D,B,C){if(D&&C!==false){if(D.version!==undefined){var A=this.version;this.version=D.version;if(this.version!==A){return this.fireEvent("versionchange",this,A,this.version)}}}},findInsertIndex:function(A){this.remoteSort=false;var B=Ext.ux.grid.livegrid.Store.superclass.findInsertIndex.call(this,A);this.remoteSort=true;if(this.bufferRange[0]<=0&&B==0){return B}else{if(this.bufferRange[0]>0&&B==0){return Number.MIN_VALUE}else{if(B>=this.bufferSize){return Number.MAX_VALUE}}}return B},sortData:function(C,D){D=D||"ASC";var A=this.fields.get(C).sortType;var B=function(F,E){var H=A(F.data[C]),G=A(E.data[C]);return H>G?1:(H<G?-1:0)};this.data.sort(D,B)},onMetaChange:function(B,A,C){this.version=null;Ext.ux.grid.livegrid.Store.superclass.onMetaChange.call(this,B,A,C)},loadRecords:function(C,A,B){this.checkVersionChange(C,A,B);if(!C){this.bufferRange=[-1,-1]}else{this.bufferRange=[A.params.start,Math.max(0,Math.min((A.params.start+A.params.limit)-1,C.totalRecords-1))]}if(A.suspendLoadEvent===true){this.suspendEvents()}Ext.ux.grid.livegrid.Store.superclass.loadRecords.call(this,C,A,B);if(A.suspendLoadEvent===true){this.resumeEvents()}},getAt:function(A){if(this.bufferRange[0]==-1){return undefined}var B=A-this.bufferRange[0];return this.data.itemAt(B)},clearFilter:function(){},isFiltered:function(){},collect:function(){},createFilterFn:function(){},sum:function(){},filter:function(){},filterBy:function(){},query:function(){},queryBy:function(){},find:function(){},findBy:function(){}});